CREATE TABLE people(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(25),
    last_name VARCHAR(25),
    age INT(2) DEFAULT 23, 
    birthday DATE,
    created_at DATETIME,
    money DECIMAL(25,2)
);