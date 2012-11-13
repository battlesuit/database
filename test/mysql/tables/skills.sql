CREATE TABLE skills(
    id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    person_id INT(11) NOT NULL,
    name VARCHAR(25),
    level INT(2)
);