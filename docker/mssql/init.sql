USE test;
GO
CREATE TABLE test (id INT PRIMARY KEY, col2 VARCHAR, col3 VARCHAR, col4 VARCHAR);
GO
INSERT INTO test VALUES
                     (1, 'a', 'b', 'c'),
                     (2, 'd', 'e', 'f'),
                     (3, 'g', 'h', 'i'),
                     (4, 'j', 'k', 'l');
GO