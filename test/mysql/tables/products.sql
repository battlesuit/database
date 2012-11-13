CREATE TABLE products(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(25),
    cent_price INT(11),
    amount INT(11),
    created_at DATETIME
);