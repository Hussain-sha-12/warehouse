CREATE DATABASE WarehouseDB;
GO

USE WarehouseDB;
GO


-- 1. Roles
CREATE TABLE Roles
(
    RoleID INT IDENTITY(1,1) PRIMARY KEY,
    RoleName VARCHAR(50) NOT NULL UNIQUE
);
GO

-- 2. Users
CREATE TABLE Users
(
    UserID INT IDENTITY(1,1) PRIMARY KEY,
    Username VARCHAR(50) NOT NULL UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL,
    RoleID INT NOT NULL,
    IsActive BIT NOT NULL DEFAULT 1,
    CreatedDate DATETIME2 NOT NULL DEFAULT GETDATE(),

    CONSTRAINT FK_Users_Roles
        FOREIGN KEY (RoleID)
        REFERENCES Roles(RoleID)
);
GO

-- 3. Categories
CREATE TABLE Categories
(
    CategoryID INT IDENTITY(1,1) PRIMARY KEY,
    CategoryName VARCHAR(100) NOT NULL UNIQUE,
    Description VARCHAR(255),
    IsActive BIT NOT NULL DEFAULT 1
);
GO

-- 4. Warehouses
CREATE TABLE Warehouses
(
    WarehouseID INT IDENTITY(1,1) PRIMARY KEY,
    WarehouseCode VARCHAR(50) NOT NULL UNIQUE,
    WarehouseName VARCHAR(100) NOT NULL,
    Location VARCHAR(255),
    IsActive BIT NOT NULL DEFAULT 1,
    CreatedDate DATETIME2 NOT NULL DEFAULT GETDATE()
);
GO

INSERT INTO Roles (RoleName)
VALUES
('Admin'),
('Manager'),
('Employee');
GO

INSERT INTO Categories (CategoryName, Description)
VALUES
('Electronics', 'Electronic products'),
('Furniture', 'Office and home furniture'),
('Stationery', 'Office stationery');
GO

INSERT INTO Warehouses
    (WarehouseCode, WarehouseName, Location)
VALUES
('WH001', 'Main Warehouse', 'Chennai'),
('WH002', 'Secondary Warehouse', 'Bangalore');
GO

SELECT * FROM Roles;
SELECT * FROM Users;
SELECT * FROM Categories;
SELECT * FROM Warehouses;

USE WarehouseDB;
GO

CREATE TABLE Suppliers
(
    SupplierID INT IDENTITY(1,1) PRIMARY KEY,
    SupplierCode VARCHAR(50) NOT NULL UNIQUE,
    SupplierName VARCHAR(100) NOT NULL,
    ContactPerson VARCHAR(100),
    Phone VARCHAR(20),
    Email VARCHAR(100),
    Address VARCHAR(255),
    IsActive BIT NOT NULL DEFAULT 1,
    CreatedDate DATETIME2 NOT NULL DEFAULT GETDATE()
);
GO

CREATE TABLE Customers
(
    CustomerID INT IDENTITY(1,1) PRIMARY KEY,
    CustomerCode VARCHAR(50) NOT NULL UNIQUE,
    CustomerName VARCHAR(100) NOT NULL,
    Phone VARCHAR(20),
    Email VARCHAR(100),
    Address VARCHAR(255),
    IsActive BIT NOT NULL DEFAULT 1,
    CreatedDate DATETIME2 NOT NULL DEFAULT GETDATE()
);
GO

CREATE TABLE Products
(
    ProductID INT IDENTITY(1,1) PRIMARY KEY,
    ProductCode VARCHAR(50) NOT NULL UNIQUE,
    ProductName VARCHAR(100) NOT NULL,

    CategoryID INT NOT NULL,

    UnitPrice DECIMAL(12,2) NOT NULL,
    ReorderLevel INT NOT NULL DEFAULT 10,

    IsActive BIT NOT NULL DEFAULT 1,
    CreatedDate DATETIME2 NOT NULL DEFAULT GETDATE(),

    CONSTRAINT FK_Products_Categories
        FOREIGN KEY (CategoryID)
        REFERENCES Categories(CategoryID),

    CONSTRAINT CK_Products_Price
        CHECK (UnitPrice >= 0),

    CONSTRAINT CK_Products_ReorderLevel
        CHECK (ReorderLevel >= 0)
);
GO

CREATE TABLE Stock
(
    StockID INT IDENTITY(1,1) PRIMARY KEY,

    ProductID INT NOT NULL,
    WarehouseID INT NOT NULL,

    Quantity INT NOT NULL DEFAULT 0,

    LastUpdated DATETIME2 NOT NULL DEFAULT GETDATE(),

    CONSTRAINT FK_Stock_Product
        FOREIGN KEY (ProductID)
        REFERENCES Products(ProductID),

    CONSTRAINT FK_Stock_Warehouse
        FOREIGN KEY (WarehouseID)
        REFERENCES Warehouses(WarehouseID),

    CONSTRAINT UQ_Stock_Product_Warehouse
        UNIQUE(ProductID, WarehouseID),

    CONSTRAINT CK_Stock_Quantity
        CHECK (Quantity >= 0)
);
GO

INSERT INTO Suppliers
(
    SupplierCode,
    SupplierName,
    ContactPerson,
    Phone,
    Email,
    Address
)
VALUES
(
    'SUP001',
    'ABC Electronics',
    'Rahul Kumar',
    '9876543210',
    'abc@gmail.com',
    'Chennai'
),
(
    'SUP002',
    'XYZ Traders',
    'Arun Kumar',
    '9876501234',
    'xyz@gmail.com',
    'Bangalore'
);
GO

INSERT INTO Customers
(
    CustomerCode,
    CustomerName,
    Phone,
    Email,
    Address
)
VALUES
(
    'CUS001',
    'Hussain',
    '9876543211',
    'hussain@gmail.com',
    'Chennai'
),
(
    'CUS002',
    'Mohammed',
    '9876543212',
    'mohammed@gmail.com',
    'Bangalore'
);
GO

INSERT INTO Products
(
    ProductCode,
    ProductName,
    CategoryID,
    UnitPrice,
    ReorderLevel
)
VALUES
(
    'PRD001',
    'Laptop',
    1,
    55000.00,
    5
),
(
    'PRD002',
    'Keyboard',
    1,
    1200.00,
    10
),
(
    'PRD003',
    'Office Chair',
    2,
    7500.00,
    5
),
(
    'PRD004',
    'Notebook',
    3,
    80.00,
    20
);
GO

SELECT * FROM Suppliers;

SELECT * FROM Customers;

SELECT * FROM Products;

SELECT * FROM Stock;

USE WarehouseDB;
GO

CREATE TABLE PurchaseOrders
(
    PurchaseOrderID INT IDENTITY(1,1) PRIMARY KEY,
    PurchaseOrderNumber VARCHAR(50) NOT NULL UNIQUE,
    SupplierID INT NOT NULL,
    WarehouseID INT NOT NULL,
    OrderDate DATETIME2 NOT NULL DEFAULT GETDATE(),

    Status VARCHAR(20) NOT NULL DEFAULT 'Pending',

    TotalAmount DECIMAL(14,2) NOT NULL DEFAULT 0,

    CreatedBy INT NULL,

    CONSTRAINT FK_PurchaseOrders_Supplier
        FOREIGN KEY (SupplierID)
        REFERENCES Suppliers(SupplierID),

    CONSTRAINT FK_PurchaseOrders_Warehouse
        FOREIGN KEY (WarehouseID)
        REFERENCES Warehouses(WarehouseID),

    CONSTRAINT FK_PurchaseOrders_User
        FOREIGN KEY (CreatedBy)
        REFERENCES Users(UserID),

    CONSTRAINT CK_PurchaseOrders_Status
        CHECK (Status IN ('Pending', 'Received', 'Cancelled')),

    CONSTRAINT CK_PurchaseOrders_Total
        CHECK (TotalAmount >= 0)
);
GO


CREATE TABLE PurchaseOrderItems
(
    PurchaseOrderItemID INT IDENTITY(1,1) PRIMARY KEY,

    PurchaseOrderID INT NOT NULL,
    ProductID INT NOT NULL,

    Quantity INT NOT NULL,
    UnitCost DECIMAL(12,2) NOT NULL,

    TotalAmount AS (Quantity * UnitCost) PERSISTED,

    CONSTRAINT FK_PurchaseItems_Order
        FOREIGN KEY (PurchaseOrderID)
        REFERENCES PurchaseOrders(PurchaseOrderID)
        ON DELETE CASCADE,

    CONSTRAINT FK_PurchaseItems_Product
        FOREIGN KEY (ProductID)
        REFERENCES Products(ProductID),

    CONSTRAINT CK_PurchaseItems_Quantity
        CHECK (Quantity > 0),

    CONSTRAINT CK_PurchaseItems_Cost
        CHECK (UnitCost >= 0)
);
GO

CREATE TABLE SalesOrders
(
    SalesOrderID INT IDENTITY(1,1) PRIMARY KEY,

    SalesOrderNumber VARCHAR(50) NOT NULL UNIQUE,

    CustomerID INT NOT NULL,
    WarehouseID INT NOT NULL,

    OrderDate DATETIME2 NOT NULL DEFAULT GETDATE(),

    Status VARCHAR(20) NOT NULL DEFAULT 'Pending',

    TotalAmount DECIMAL(14,2) NOT NULL DEFAULT 0,

    CreatedBy INT NULL,

    CONSTRAINT FK_SalesOrders_Customer
        FOREIGN KEY (CustomerID)
        REFERENCES Customers(CustomerID),

    CONSTRAINT FK_SalesOrders_Warehouse
        FOREIGN KEY (WarehouseID)
        REFERENCES Warehouses(WarehouseID),

    CONSTRAINT FK_SalesOrders_User
        FOREIGN KEY (CreatedBy)
        REFERENCES Users(UserID),

    CONSTRAINT CK_SalesOrders_Status
        CHECK (Status IN ('Pending', 'Completed', 'Cancelled')),

    CONSTRAINT CK_SalesOrders_Total
        CHECK (TotalAmount >= 0)
);
GO

CREATE TABLE SalesOrderItems
(
    SalesOrderItemID INT IDENTITY(1,1) PRIMARY KEY,

    SalesOrderID INT NOT NULL,
    ProductID INT NOT NULL,

    Quantity INT NOT NULL,
    UnitPrice DECIMAL(12,2) NOT NULL,

    TotalAmount AS (Quantity * UnitPrice) PERSISTED,

    CONSTRAINT FK_SalesItems_Order
        FOREIGN KEY (SalesOrderID)
        REFERENCES SalesOrders(SalesOrderID)
        ON DELETE CASCADE,

    CONSTRAINT FK_SalesItems_Product
        FOREIGN KEY (ProductID)
        REFERENCES Products(ProductID),

    CONSTRAINT CK_SalesItems_Quantity
        CHECK (Quantity > 0),

    CONSTRAINT CK_SalesItems_Price
        CHECK (UnitPrice >= 0)
);
GO

CREATE TABLE StockTransactions
(
    TransactionID BIGINT IDENTITY(1,1) PRIMARY KEY,

    ProductID INT NOT NULL,
    WarehouseID INT NOT NULL,

    TransactionType VARCHAR(20) NOT NULL,

    Quantity INT NOT NULL,

    ReferenceType VARCHAR(30),
    ReferenceID INT NULL,

    TransactionDate DATETIME2 NOT NULL DEFAULT GETDATE(),

    CreatedBy INT NULL,

    CONSTRAINT FK_StockTransactions_Product
        FOREIGN KEY (ProductID)
        REFERENCES Products(ProductID),

    CONSTRAINT FK_StockTransactions_Warehouse
        FOREIGN KEY (WarehouseID)
        REFERENCES Warehouses(WarehouseID),

    CONSTRAINT FK_StockTransactions_User
        FOREIGN KEY (CreatedBy)
        REFERENCES Users(UserID),

    CONSTRAINT CK_StockTransactions_Type
        CHECK (TransactionType IN ('IN', 'OUT', 'ADJUSTMENT')),

    CONSTRAINT CK_StockTransactions_Quantity
        CHECK (Quantity > 0)
);
GO

SELECT TABLE_NAME
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_TYPE = 'BASE TABLE'
ORDER BY TABLE_NAME;


CREATE OR ALTER PROCEDURE sp_StockIn
(
    @ProductID INT,
    @WarehouseID INT,
    @Quantity INT,
    @ReferenceType VARCHAR(30) = 'PURCHASE',
    @ReferenceID INT = NULL,
    @CreatedBy INT = NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    BEGIN TRY

        BEGIN TRANSACTION;

        IF @Quantity <= 0
            THROW 50001, 'Quantity must be greater than zero.', 1;

        IF NOT EXISTS
        (
            SELECT 1
            FROM Products
            WHERE ProductID = @ProductID
              AND IsActive = 1
        )
            THROW 50002, 'Product does not exist or is inactive.', 1;

        IF NOT EXISTS
        (
            SELECT 1
            FROM Warehouses
            WHERE WarehouseID = @WarehouseID
              AND IsActive = 1
        )
            THROW 50003, 'Warehouse does not exist or is inactive.', 1;

        IF EXISTS
        (
            SELECT 1
            FROM Stock
            WHERE ProductID = @ProductID
              AND WarehouseID = @WarehouseID
        )
        BEGIN

            UPDATE Stock
            SET
                Quantity = Quantity + @Quantity,
                LastUpdated = GETDATE()
            WHERE ProductID = @ProductID
              AND WarehouseID = @WarehouseID;

        END
        ELSE
        BEGIN

            INSERT INTO Stock
            (
                ProductID,
                WarehouseID,
                Quantity
            )
            VALUES
            (
                @ProductID,
                @WarehouseID,
                @Quantity
            );

        END;

        INSERT INTO StockTransactions
        (
            ProductID,
            WarehouseID,
            TransactionType,
            Quantity,
            ReferenceType,
            ReferenceID,
            CreatedBy
        )
        VALUES
        (
            @ProductID,
            @WarehouseID,
            'IN',
            @Quantity,
            @ReferenceType,
            @ReferenceID,
            @CreatedBy
        );

        COMMIT TRANSACTION;

        SELECT
            'Stock In Successful' AS Message,
            @ProductID AS ProductID,
            @Quantity AS AddedQuantity;

    END TRY

    BEGIN CATCH

        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        THROW;

    END CATCH
END;
GO

--EXEC sp_StockIn
--    @ProductID = 1,
--    @WarehouseID = 1,
--    @Quantity = 50,
--    @ReferenceType = 'PURCHASE',
--    @ReferenceID = NULL,
--    @CreatedBy = 1;
--
--SELECT *
--FROM Stock
--WHERE ProductID = 1
--  AND WarehouseID = 1;


SELECT *
FROM Users;

USE WarehouseDB;
GO

SELECT * FROM Roles;



INSERT INTO Users
(
    Username,
    PasswordHash,
    RoleID
)
VALUES
(
    'admin',
    'TEMP_PASSWORD',
    1
);
GO

SELECT
    UserID,
    Username,
    RoleID
FROM Users;

EXEC sp_StockIn
    @ProductID = 1,
    @WarehouseID = 1,
    @Quantity = 50,
    @ReferenceType = 'PURCHASE',
    @ReferenceID = NULL,
    @CreatedBy = 1;

SELECT
    p.ProductName,
    w.WarehouseName,
    s.Quantity
FROM Stock s
JOIN Products p
    ON s.ProductID = p.ProductID
JOIN Warehouses w
    ON s.WarehouseID = w.WarehouseID;


USE WarehouseDB;
GO

SELECT *
FROM Products;

SELECT *
FROM Products
ORDER BY ProductID DESC;

SELECT
    fk.name AS ForeignKeyName,
    OBJECT_NAME(fk.parent_object_id) AS ReferencingTable,
    COL_NAME(fkc.parent_object_id, fkc.parent_column_id) AS ReferencingColumn,
    OBJECT_NAME(fk.referenced_object_id) AS ReferencedTable,
    COL_NAME(fkc.referenced_object_id, fkc.referenced_column_id) AS ReferencedColumn
FROM sys.foreign_keys fk
INNER JOIN sys.foreign_key_columns fkc
    ON fk.object_id = fkc.constraint_object_id
WHERE OBJECT_NAME(fk.referenced_object_id) = 'Products';




SELECT *
FROM Products
ORDER BY ProductID DESC;

SELECT ProductID, ProductCode, ProductName, IsActive
FROM Products
ORDER BY ProductID DESC;

SELECT ProductID, ProductCode, ProductName, IsActive
FROM Products
WHERE ProductID = 5;



EXEC sp_help 'Categories';

SELECT *
FROM Categories;


SELECT *
FROM Categories
ORDER BY CategoryID;

SELECT CategoryID, CategoryName, Description, IsActive
FROM Categories
ORDER BY CategoryID;

UPDATE Categories
SET IsActive = 0
WHERE CategoryID = 1;

SELECT CategoryID, CategoryName, Description, IsActive
FROM Categories
WHERE CategoryID = 1;

SELECT CategoryID, CategoryName, IsActive
FROM Categories
WHERE CategoryID = 2;

USE WarehouseDB;
GO

SELECT CategoryID, CategoryName, Description, IsActive
FROM Categories
ORDER BY CategoryID;

SELECT CategoryID, CategoryName, IsActive
FROM Categories
WHERE CategoryID = 2;

SELECT *
FROM Suppliers;

SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'PurchaseOrders'
ORDER BY ORDINAL_POSITION;

SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    DATA_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME LIKE '%Purchase%'
ORDER BY TABLE_NAME, ORDINAL_POSITION;


SELECT COLUMN_NAME, DATA_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'Products'
ORDER BY ORDINAL_POSITION;



USE WarehouseDB;
GO

SELECT
    COLUMN_NAME,
    DATA_TYPE,
    CHARACTER_MAXIMUM_LENGTH,
    NUMERIC_PRECISION,
    NUMERIC_SCALE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'Inventory'
ORDER BY ORDINAL_POSITION;


USE WarehouseDB;
GO

SELECT
    TABLE_SCHEMA,
    TABLE_NAME
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_NAME LIKE '%Inventory%';

USE WarehouseDB;
GO

CREATE TABLE Inventory
(
    InventoryID INT IDENTITY(1,1) PRIMARY KEY,

    ProductID INT NOT NULL,

    Quantity INT NOT NULL DEFAULT 0,

    ReorderLevel INT NOT NULL DEFAULT 0,

    LastUpdated DATETIME2 NOT NULL DEFAULT GETDATE(),

    CONSTRAINT FK_Inventory_Product
        FOREIGN KEY (ProductID)
        REFERENCES Products(ProductID),

    CONSTRAINT UQ_Inventory_Product
        UNIQUE (ProductID),

    CONSTRAINT CK_Inventory_Quantity
        CHECK (Quantity >= 0),

    CONSTRAINT CK_Inventory_ReorderLevel
        CHECK (ReorderLevel >= 0)
);
GO

SELECT * FROM Inventory;

USE WarehouseDB;
GO

SELECT *
FROM Users;
GO

USE WarehouseDB;
GO

SELECT
    COLUMN_NAME,
    DATA_TYPE,
    CHARACTER_MAXIMUM_LENGTH
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'Users'
ORDER BY ORDINAL_POSITION;
GO

USE WarehouseDB;
GO

SELECT *
FROM Users;
GO

SELECT TABLE_NAME
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_NAME LIKE '%Role%';
GO

SELECT *
FROM Roles;
GO

USE WarehouseDB;
GO

SELECT
    UserID,
    Username,
    PasswordHash,
    RoleID,
    IsActive,
    CreatedDate
FROM Users
WHERE Username = 'admin';
GO


USE WarehouseDB;
GO

SELECT
    COLUMN_NAME,
    DATA_TYPE,
    CHARACTER_MAXIMUM_LENGTH,
    IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'StockTransactions'
ORDER BY ORDINAL_POSITION;

SELECT TOP 10 *
FROM StockTransactions
ORDER BY StockTransactionID DESC;

SELECT
    PurchaseOrderID,
    PurchaseOrderNumber,
    WarehouseID,
    Status
FROM PurchaseOrders
ORDER BY PurchaseOrderID DESC;



SELECT *
FROM StockTransactions
WHERE TransactionType = 'IN'
ORDER BY TransactionDate DESC;

SELECT *
FROM Inventory
ORDER BY InventoryID DESC;

SELECT TOP 10 *
FROM StockTransactions
WHERE TransactionType = 'OUT'
ORDER BY TransactionDate DESC;

SELECT *
FROM Inventory
WHERE ProductID = YOUR_PRODUCT_ID;

SELECT *
FROM Inventory
WHERE ProductID = 7;

SELECT
    i.InventoryID,
    i.ProductID,
    p.ProductCode,
    p.ProductName,
    i.Quantity,
    i.ReorderLevel,
    i.LastUpdated
FROM Inventory i
INNER JOIN Products p
    ON p.ProductID = i.ProductID
ORDER BY i.ProductID;

SELECT TOP 10
    TransactionID,
    ProductID,
    WarehouseID,
    TransactionType,
    Quantity,
    ReferenceType,
    ReferenceID,
    TransactionDate,
    CreatedBy
FROM StockTransactions
WHERE TransactionType = 'OUT'
ORDER BY TransactionDate DESC;

SELECT
    cc.name AS ConstraintName,
    cc.definition AS AllowedValues
FROM sys.check_constraints cc
WHERE cc.name = 'CK_PurchaseOrders_Status';

SELECT
    PurchaseOrderID,
    PurchaseOrderNumber,
    Status
FROM PurchaseOrders
ORDER BY PurchaseOrderID DESC;

SELECT
    InventoryID,
    ProductID,
    Quantity,
    ReorderLevel,
    LastUpdated
FROM Inventory
ORDER BY InventoryID DESC;

SELECT TOP 10
    TransactionID,
    ProductID,
    WarehouseID,
    TransactionType,
    Quantity,
    ReferenceType,
    ReferenceID,
    TransactionDate,
    CreatedBy
FROM StockTransactions
WHERE TransactionType = 'IN'
ORDER BY TransactionDate DESC;

SELECT
    PurchaseOrderID,
    PurchaseOrderNumber,
    Status
FROM PurchaseOrders
ORDER BY PurchaseOrderID DESC;

SELECT
    InventoryID,
    ProductID,
    Quantity,
    ReorderLevel,
    LastUpdated
FROM Inventory
ORDER BY InventoryID DESC;

SELECT
    po.PurchaseOrderNumber,
    poi.PurchaseOrderItemID,
    poi.ProductID,
    poi.Quantity AS OrderedQuantity,
    ISNULL(SUM(st.Quantity), 0) AS ReceivedQuantity,
    poi.Quantity - ISNULL(SUM(st.Quantity), 0) AS RemainingQuantity
FROM PurchaseOrders po
INNER JOIN PurchaseOrderItems poi
    ON po.PurchaseOrderID = poi.PurchaseOrderID
LEFT JOIN StockTransactions st
    ON st.ReferenceID = poi.PurchaseOrderItemID
    AND st.ReferenceType = 'PurchaseOrderItem'
    AND st.TransactionType = 'IN'
GROUP BY
    po.PurchaseOrderNumber,
    poi.PurchaseOrderItemID,
    poi.ProductID,
    poi.Quantity
ORDER BY
    po.PurchaseOrderNumber,
    poi.PurchaseOrderItemID;

SELECT TOP 10
    TransactionID,
    ProductID,
    WarehouseID,
    TransactionType,
    Quantity,
    ReferenceType,
    ReferenceID,
    TransactionDate,
    CreatedBy
FROM StockTransactions
WHERE TransactionType = 'OUT'
ORDER BY TransactionDate DESC;

SELECT
    InventoryID,
    ProductID,
    Quantity,
    ReorderLevel,
    LastUpdated
FROM Inventory
ORDER BY InventoryID DESC;

SELECT
    InventoryID,
    ProductID,
    Quantity,
    ReorderLevel,
    LastUpdated
FROM Inventory
ORDER BY InventoryID DESC;

USE WarehouseDB;
GO

/* =========================================================
   SALES TABLES
   ========================================================= */

IF OBJECT_ID('dbo.SalesOrders', 'U') IS NULL
BEGIN

    CREATE TABLE SalesOrders
    (
        SalesOrderID INT IDENTITY(1,1) PRIMARY KEY,

        SalesOrderNumber VARCHAR(50)
            NOT NULL UNIQUE,

        CustomerName VARCHAR(100)
            NOT NULL,

        WarehouseID INT
            NOT NULL,

        OrderDate DATETIME2
            NOT NULL DEFAULT GETDATE(),

        Status VARCHAR(20)
            NOT NULL DEFAULT 'Pending',

        TotalAmount DECIMAL(18,2)
            NOT NULL DEFAULT 0,

        CreatedBy INT NULL,

        CONSTRAINT CK_SalesOrders_Status
            CHECK
            (
                Status IN
                (
                    'Pending',
                    'Completed',
                    'Cancelled'
                )
            )
    );

END;
GO


IF OBJECT_ID('dbo.SalesOrderItems', 'U') IS NULL
BEGIN

    CREATE TABLE SalesOrderItems
    (
        SalesOrderItemID INT IDENTITY(1,1) PRIMARY KEY,

        SalesOrderID INT NOT NULL,

        ProductID INT NOT NULL,

        Quantity INT NOT NULL,

        UnitPrice DECIMAL(18,2) NOT NULL,

        TotalAmount AS
            (Quantity * UnitPrice),

        CONSTRAINT FK_SalesOrderItems_Order
            FOREIGN KEY (SalesOrderID)
            REFERENCES SalesOrders(SalesOrderID),

        CONSTRAINT FK_SalesOrderItems_Product
            FOREIGN KEY (ProductID)
            REFERENCES Products(ProductID),

        CONSTRAINT CK_SalesOrderItems_Quantity
            CHECK (Quantity > 0)
    );

END;
GO


/* =========================================================
   INDEXES
   ========================================================= */

IF NOT EXISTS
(
    SELECT 1
    FROM sys.indexes
    WHERE name = 'IX_SalesOrders_OrderDate'
      AND object_id = OBJECT_ID('dbo.SalesOrders')
)
BEGIN

    CREATE INDEX IX_SalesOrders_OrderDate
    ON SalesOrders(OrderDate);

END;
GO


IF NOT EXISTS
(
    SELECT 1
    FROM sys.indexes
    WHERE name = 'IX_SalesOrderItems_ProductID'
      AND object_id = OBJECT_ID('dbo.SalesOrderItems')
)
BEGIN

    CREATE INDEX IX_SalesOrderItems_ProductID
    ON SalesOrderItems(ProductID);

END;
GO

USE WarehouseDB;
GO

SELECT
    TABLE_NAME
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_NAME IN
(
    'SalesOrders',
    'SalesOrderItems'
);

USE WarehouseDB;
GO

SELECT
    TABLE_NAME,
    COLUMN_NAME,
    DATA_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME IN
(
    'SalesOrders',
    'SalesOrderItems'
) 
ORDER BY
    TABLE_NAME,
    ORDINAL_POSITION;


USE WarehouseDB;
GO

SELECT
    TABLE_NAME,
    COLUMN_NAME,
    DATA_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME LIKE '%Customer%'
ORDER BY
    TABLE_NAME,
    ORDINAL_POSITION;

USE WarehouseDB;
GO

SELECT
    COLUMN_NAME,
    DATA_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'StockTransactions'
ORDER BY ORDINAL_POSITION;

SELECT
    COLUMN_NAME,
    DATA_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'Warehouses'
ORDER BY ORDINAL_POSITION;


OFFSET {$offset} ROWS
FETCH NEXT {$perPage} ROWS ONLY


USE WarehouseDB;
GO

CREATE TABLE WarehouseInventory
(
    WarehouseInventoryID INT IDENTITY(1,1) PRIMARY KEY,

    ProductID INT NOT NULL,

    WarehouseID INT NOT NULL,

    Quantity INT NOT NULL DEFAULT 0,

    ReorderLevel INT NOT NULL DEFAULT 0,

    LastUpdated DATETIME2 NOT NULL DEFAULT GETDATE(),

    CONSTRAINT FK_WarehouseInventory_Product
        FOREIGN KEY (ProductID)
        REFERENCES Products(ProductID),

    CONSTRAINT FK_WarehouseInventory_Warehouse
        FOREIGN KEY (WarehouseID)
        REFERENCES Warehouses(WarehouseID),

    CONSTRAINT UQ_WarehouseInventory_Product_Warehouse
        UNIQUE (ProductID, WarehouseID),

    CONSTRAINT CK_WarehouseInventory_Quantity
        CHECK (Quantity >= 0),

    CONSTRAINT CK_WarehouseInventory_ReorderLevel
        CHECK (ReorderLevel >= 0)
);
GO

SELECT *
FROM WarehouseInventory;

USE WarehouseDB;
GO

INSERT INTO WarehouseInventory
(
    ProductID,
    WarehouseID,
    Quantity,
    ReorderLevel,
    LastUpdated
)
SELECT
    i.ProductID,
    w.WarehouseID,
    i.Quantity,
    i.ReorderLevel,
    GETDATE()
FROM Inventory i
CROSS JOIN
(
    SELECT TOP 1 WarehouseID
    FROM Warehouses
    WHERE WarehouseCode = 'WH001'
      AND IsActive = 1
) w
WHERE i.Quantity > 0
  AND NOT EXISTS
  (
      SELECT 1
      FROM WarehouseInventory wi
      WHERE wi.ProductID = i.ProductID
        AND wi.WarehouseID = w.WarehouseID
  );
GO


SELECT
    wi.WarehouseInventoryID,
    p.ProductCode,
    p.ProductName,
    w.WarehouseCode,
    w.WarehouseName,
    wi.Quantity,
    wi.ReorderLevel
FROM WarehouseInventory wi
INNER JOIN Products p
    ON p.ProductID = wi.ProductID
INNER JOIN Warehouses w
    ON w.WarehouseID = wi.WarehouseID
ORDER BY p.ProductName;


USE WarehouseDB;
GO

CREATE TABLE ReorderRequests
(
    ReorderRequestID INT IDENTITY(1,1) PRIMARY KEY,

    ProductID INT NOT NULL,

    WarehouseID INT NOT NULL,

    RequestedQuantity INT NOT NULL,

    RequestDate DATETIME2 NOT NULL
        DEFAULT GETDATE(),

    Status VARCHAR(30) NOT NULL
        DEFAULT 'Requested',

    RequestedBy INT NULL,

    ApprovedBy INT NULL,

    ApprovedDate DATETIME2 NULL,

    PurchaseOrderID INT NULL,

    CompletedDate DATETIME2 NULL,

    Notes VARCHAR(500) NULL,

    CONSTRAINT FK_ReorderRequests_Product
        FOREIGN KEY (ProductID)
        REFERENCES Products(ProductID),

    CONSTRAINT FK_ReorderRequests_Warehouse
        FOREIGN KEY (WarehouseID)
        REFERENCES Warehouses(WarehouseID),

    CONSTRAINT FK_ReorderRequests_RequestedBy
        FOREIGN KEY (RequestedBy)
        REFERENCES Users(UserID),

    CONSTRAINT FK_ReorderRequests_ApprovedBy
        FOREIGN KEY (ApprovedBy)
        REFERENCES Users(UserID),

    CONSTRAINT CK_ReorderRequests_Quantity
        CHECK (RequestedQuantity > 0),

    CONSTRAINT CK_ReorderRequests_Status
        CHECK
        (
            Status IN
            (
                'Requested',
                'Approved',
                'Rejected',
                'Purchase Ordered',
                'Received',
                'Completed'
            )
        )
);
GO

SELECT *
FROM ReorderRequests;


USE WarehouseDB;
GO

CREATE UNIQUE INDEX UX_ReorderRequests_Active
ON ReorderRequests
(
    ProductID,
    WarehouseID
)
WHERE Status IN
(
    'Requested',
    'Approved',
    'Purchase Ordered',
    'Received'
);
GO


SELECT
    name,
    type_desc,
    is_unique
FROM sys.indexes
WHERE object_id = OBJECT_ID('ReorderRequests');



SELECT *
FROM ReorderRequests
ORDER BY ReorderRequestID DESC;








SELECT
    ReorderRequestID,
    ProductID,
    WarehouseID,
    RequestedQuantity,
    Status,
    RequestedBy,
    RequestDate,
    Notes
FROM ReorderRequests
WHERE ProductID = 1
  AND WarehouseID = 1
ORDER BY ReorderRequestID DESC;


SELECT
    ReorderRequestID,
    ProductID,
    WarehouseID,
    RequestedQuantity,
    Status,
    RequestedBy,
    ApprovedBy,
    ApprovedDate,
    PurchaseOrderID,
    CompletedDate,
    Notes
FROM ReorderRequests
ORDER BY ReorderRequestID DESC;

SELECT
    ReorderRequestID,
    ProductID,
    WarehouseID,
    RequestedQuantity,
    Status,
    RequestedBy,
    ApprovedBy,
    ApprovedDate,
    PurchaseOrderID,
    CompletedDate,
    Notes
FROM ReorderRequests
ORDER BY ReorderRequestID DESC;


USE WarehouseDB;
GO

SELECT
    TABLE_NAME,
    COLUMN_NAME,
    DATA_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME IN ('PurchaseOrders', 'PurchaseOrderItems')
ORDER BY TABLE_NAME, ORDINAL_POSITION;
GO

USE WarehouseDB;
GO

SELECT
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'Products'
ORDER BY ORDINAL_POSITION;
GO

USE WarehouseDB;
GO

SELECT
    TABLE_NAME,
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'Suppliers'
ORDER BY ORDINAL_POSITION;
GO

USE WarehouseDB;
GO

SELECT
    TABLE_NAME,
    COLUMN_NAME,
    DATA_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE COLUMN_NAME IN ('ProductID', 'SupplierID')
ORDER BY TABLE_NAME, ORDINAL_POSITION;
GO

USE WarehouseDB;
GO

CREATE TABLE ProductSuppliers
(
    ProductSupplierID INT IDENTITY(1,1) PRIMARY KEY,
    ProductID INT NOT NULL,
    SupplierID INT NOT NULL,
    UnitCost DECIMAL(18,2) NOT NULL DEFAULT 0,
    IsPreferred BIT NOT NULL DEFAULT 0,
    IsActive BIT NOT NULL DEFAULT 1,
    CreatedDate DATETIME2 NOT NULL DEFAULT GETDATE(),

    CONSTRAINT FK_ProductSuppliers_Product
        FOREIGN KEY (ProductID)
        REFERENCES Products(ProductID),

    CONSTRAINT FK_ProductSuppliers_Supplier
        FOREIGN KEY (SupplierID)
        REFERENCES Suppliers(SupplierID),

    CONSTRAINT UQ_ProductSuppliers_Product_Supplier
        UNIQUE (ProductID, SupplierID),

    CONSTRAINT CK_ProductSuppliers_UnitCost
        CHECK (UnitCost >= 0)
);
GO

SELECT * 
FROM ProductSuppliers;
GO

SELECT
    ProductID,
    ProductCode,
    ProductName
FROM Products
WHERE IsActive = 1
ORDER BY ProductID;
GO

SELECT
    SupplierID,
    SupplierCode,
    SupplierName
FROM Suppliers
WHERE IsActive = 1
ORDER BY SupplierID;
GO

USE WarehouseDB;
GO

INSERT INTO ProductSuppliers
(
    ProductID,
    SupplierID,
    UnitCost,
    IsPreferred
)
VALUES
(1, 1, 45000.00, 1),  -- Laptop -> ABC Electronics
(1, 2, 46000.00, 0),  -- Laptop -> XYZ Traders
(2, 1, 1200.00, 1),   -- Keyboard -> ABC Electronics
(3, 2, 3500.00, 1);   -- Office Chair -> XYZ Traders
GO

SELECT
    ps.ProductSupplierID,
    p.ProductCode,
    p.ProductName,
    s.SupplierCode,
    s.SupplierName,
    ps.UnitCost,
    ps.IsPreferred
FROM ProductSuppliers ps
INNER JOIN Products p
    ON p.ProductID = ps.ProductID
INNER JOIN Suppliers s
    ON s.SupplierID = ps.SupplierID
ORDER BY p.ProductID, ps.IsPreferred DESC;
GO

USE WarehouseDB;
GO

SELECT
    PurchaseOrderID,
    PurchaseOrderNumber,
    SupplierID,
    WarehouseID,
    OrderDate,
    Status,
    TotalAmount,
    CreatedBy
FROM PurchaseOrders
ORDER BY PurchaseOrderID DESC;
GO

SELECT
    PurchaseOrderItemID,
    PurchaseOrderID,
    ProductID,
    Quantity,
    UnitCost,
    TotalAmount
FROM PurchaseOrderItems
ORDER BY PurchaseOrderItemID DESC;
GO

USE WarehouseDB;
GO

SELECT
    wi.WarehouseInventoryID,
    p.ProductCode,
    p.ProductName,
    w.WarehouseCode,
    w.WarehouseName,
    wi.Quantity,
    wi.ReorderLevel,
    wi.LastUpdated
FROM WarehouseInventory wi
INNER JOIN Products p
    ON p.ProductID = wi.ProductID
INNER JOIN Warehouses w
    ON w.WarehouseID = wi.WarehouseID
ORDER BY wi.WarehouseInventoryID;


USE WarehouseDB;
GO

ALTER TABLE ReorderRequests
ADD
    ReceivedQuantity INT NOT NULL DEFAULT 0,
    RemainingQuantity INT NOT NULL DEFAULT 0;
GO

UPDATE ReorderRequests
SET
    ReceivedQuantity =
        CASE
            WHEN Status IN ('Received', 'Completed')
                THEN RequestedQuantity
            ELSE 0
        END,
    RemainingQuantity =
        CASE 
            WHEN Status IN ('Received', 'Completed')
                THEN 0
            ELSE RequestedQuantity
        END;
GO

SELECT
    ReorderRequestID,
    RequestedQuantity,
    ReceivedQuantity,
    RemainingQuantity,
    Status
FROM ReorderRequests
ORDER BY ReorderRequestID;

USE WarehouseDB;
GO

SELECT
    ReorderRequestID,
    RequestedQuantity,
    ReceivedQuantity,
    RemainingQuantity,
    Status
FROM ReorderRequests
WHERE ReorderRequestID = 4;


USE WarehouseDB;
GO

SELECT
    ReorderRequestID,
    ProductID,
    WarehouseID,
    RequestedQuantity,
    ReceivedQuantity,
    RemainingQuantity,
    Status
FROM ReorderRequests
WHERE ReorderRequestID IN (4, 5);

USE WarehouseDB;
GO

UPDATE ReorderRequests
SET
    Status = 'Purchase Ordered',
    ReceivedQuantity = 0,
    RemainingQuantity = RequestedQuantity,
    CompletedDate = NULL
WHERE ReorderRequestID IN (4, 5);
GO

USE WarehouseDB;
GO

SELECT
    ReorderRequestID,
    ProductID,
    WarehouseID,
    RequestedQuantity,
    ReceivedQuantity,
    RemainingQuantity,
    Status
FROM ReorderRequests
ORDER BY ReorderRequestID;
GO

SELECT
    WarehouseInventoryID,
    ProductID,
    WarehouseID,
    Quantity
FROM WarehouseInventory
ORDER BY WarehouseID, ProductID;
GO

USE WarehouseDB;
GO

UPDATE ReorderRequests
SET
    ReceivedQuantity = 0,
    RemainingQuantity = RequestedQuantity
WHERE ReorderRequestID = 7;
GO

SELECT
    ReorderRequestID,
    RequestedQuantity,
    ReceivedQuantity,
    RemainingQuantity,
    Status
FROM ReorderRequests
WHERE ReorderRequestID = 7;


USE WarehouseDB;
GO

UPDATE ReorderRequests
SET
    Status = 'Received',
    ReceivedQuantity = 10,
    RemainingQuantity = 10,
    CompletedDate = NULL
WHERE ReorderRequestID = 7;
GO

SELECT
    ReorderRequestID,
    RequestedQuantity,
    ReceivedQuantity,
    RemainingQuantity,
    Status
FROM ReorderRequests
WHERE ReorderRequestID = 7;


USE WarehouseDB;
GO

UPDATE ReorderRequests
SET
    ReceivedQuantity = 0,
    RemainingQuantity = RequestedQuantity,
    Status = 'Purchase Ordered',
    CompletedDate = NULL
WHERE ReorderRequestID = 8;
GO

USE WarehouseDB;
GO

UPDATE ReorderRequests
SET
    ReceivedQuantity = 0,
    RemainingQuantity = RequestedQuantity
WHERE ReorderRequestID = 8;
GO

USE WarehouseDB;
GO

SELECT
    ReorderRequestID,
    RequestedQuantity,
    ReceivedQuantity,
    RemainingQuantity,
    Status
FROM ReorderRequests
WHERE ReorderRequestID = 8;
GO

SELECT
    ReorderRequestID,
    RequestedQuantity,
    ReceivedQuantity,
    RemainingQuantity,
    Status
FROM ReorderRequests
WHERE ReorderRequestID = 8;

USE WarehouseDB;
GO

SELECT
    ReorderRequestID,
    RequestedQuantity,
    ReceivedQuantity,
    RemainingQuantity,
    Status
FROM ReorderRequests
WHERE ReorderRequestID = 8;

SELECT
    ReorderRequestID,
    RequestedQuantity,
    ReceivedQuantity,
    RemainingQuantity,
    Status
FROM ReorderRequests
WHERE ReorderRequestID = 8;

UPDATE ReorderRequests
SET
    ReceivedQuantity = 10,
    RemainingQuantity = 40,
    Status = 'Received'
WHERE ReorderRequestID = 8;

UPDATE WarehouseInventory
SET
    Quantity = Quantity + 10,
    LastUpdated = GETDATE()
WHERE ProductID = 2
  AND WarehouseID = 1;

SELECT
    ReorderRequestID,
    RequestedQuantity,
    ReceivedQuantity,
    RemainingQuantity,
    Status
FROM ReorderRequests
WHERE ReorderRequestID = 8;

USE WarehouseDB;
GO

SELECT
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'SalesOrders'
ORDER BY ORDINAL_POSITION;

USE WarehouseDB;
GO

ALTER TABLE SalesOrders
ADD
    DispatchStatus VARCHAR(30) NOT NULL DEFAULT 'Pending',
    DispatchDate DATETIME2 NULL,
    DeliveredDate DATETIME2 NULL,
    DispatchedBy INT NULL,
    DeliveryNotes VARCHAR(500) NULL;
GO

SELECT *
FROM Products
WHERE ProductCode = 'PRD004';

SELECT *
FROM Categories
WHERE CategoryName = 'Computer Accessories';

SELECT TOP 10
    ReorderRequestID,
    RequestedQuantity,
    ReceivedQuantity,
    RemainingQuantity,
    Status
FROM ReorderRequests
ORDER BY ReorderRequestID DESC;

UPDATE ReorderRequests
SET RemainingQuantity = RequestedQuantity - ReceivedQuantity

WHERE ReorderRequestID = 9;


SELECT
    ReorderRequestID,
    RequestedQuantity,
    ReceivedQuantity,
    RemainingQuantity,
    Status
FROM ReorderRequests
WHERE ReorderRequestID = 9;