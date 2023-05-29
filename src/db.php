<?php
    class User {
        public $id;
        public $name;
        public $pwd_hash;

        public function __construct($id, $name, $pwd) {
            $this->id = $id;
            $this->name = $name;
            $this->pwd_hash = $pwd;
        }
    }

    class Db {
        private $_conn;

        public function __construct($dbname) {
            Db::createDatabase($dbname);

            try {
                $this->_conn = new PDO("mysql:host=mysql;dbname=$dbname", "root", "root");
                $this->_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                Db::createTableUsers($this->_conn);
                Db::createTableTimestamps($this->_conn);
                Db::createTableCurrencies($this->_conn);
            } catch (PDOException $e) {
                die("Error: Couldn't connect to database (Cause: " . $e->getMessage() . ")");
            }
        }

        private static function createDatabase($dbname) {
            try {
                $conn = new PDO("mysql:host=mysql;", "root", "root");
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
                $conn->exec($sql);
            } catch (PDOException $e) {
                die("Error: Couldn't create database (Cause: " . $e->getMessage() . ")");
            }
        }

        private static function createTableUsers($conn) {
            $sql = "CREATE TABLE IF NOT EXISTS Users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                login VARCHAR(255) NOT NULL UNIQUE,
                pwd_hash VARCHAR(255) NOT NULL   
            )";

            try {
                $conn->exec($sql);
                // echo "Table users created successfully.\n";
            } catch (PDOException $e) {
                die("Error: Couldn't create table Users (Cause: " . $e->getMessage() . ")");
            }
        }

        private static function createTableTimestamps($conn) {
            $sql = "CREATE TABLE IF NOT EXISTS Timestamps (
                id INT PRIMARY KEY AUTO_INCREMENT,
                ts TIMESTAMP NOT NULL
            )";

            try {
                $conn->exec($sql);
                // echo "Table timestamps created successfully.\n";
            } catch (PDOException $e) {
                die("Error: Couldn't create table Timestamps (Cause: " . $e->getMessage() . ")");
            }
        }

        private static function createTableCurrencies($conn) {
            $sql = "CREATE TABLE IF NOT EXISTS Currencies (
                id INT PRIMARY KEY AUTO_INCREMENT,
                char_code VARCHAR(64) NOT NULL,
                nominal INT,
                name VARCHAR(128) NOT NULL,
                value VARCHAR(128) NOT NULL,
                ts_id INT,
                FOREIGN KEY (ts_id) REFERENCES Timestamps (id)
            )";

            try {
                $conn->exec($sql);
                // echo "Table currencies created successfully.\n";
            } catch (PDOException $e) {
                die("Error: Couldn't create table Currencies (Cause: " . $e->getMessage() . ")");
            }
        }

        public function createUser($username, $pwd) {
            $sql = "INSERT INTO Users (login, pwd_hash) VALUES (:uname, :pwd_hash)";
            $pwd_hash = password_hash($pwd, PASSWORD_DEFAULT);

            try {
                $stmt = $this->_conn->prepare($sql);

                $stmt->bindParam(":uname", $username, PDO::PARAM_STR);
                $stmt->bindParam(":pwd_hash", $pwd_hash, PDO::PARAM_STR);

                $stmt->execute();
            } catch (PDOException $e) {
                die("Error: Couldn't create new user (Cause: " . $e->getMessage() . ")");
            }
        }

        public function getUser($username) {
            $sql = "SELECT * FROM Users WHERE login = :uname";

            try {
                $stmt = $this->_conn->prepare($sql);
                $stmt->bindParam(":uname", $username, PDO::PARAM_STR);
                $stmt->execute();

                if ($stmt->rowCount() == 1 && $row = $stmt->fetch()) {
                    return new User($row["id"], $row["login"], $row["pwd_hash"]);
                }
            } catch (PDOException $e) {
                die("Error: Couldn't retrieve user $username (Cause: " . $e->getMessage() . ")");
            }

            return null;
        }

        public function checkUserExists($username) {
            $exist = false;
            $sql = "SELECT id FROM Users WHERE login = :uname";

            try {
                $stmt = $this->_conn->prepare($sql);
                $stmt->bindParam(":uname", $username, PDO::PARAM_STR);
                $stmt->execute();

                $exist = $stmt->rowCount() == 1;
            } catch (PDOException $e) {
                die("Error: Couldn't retrieve user $username (Cause: " . $e->getMessage() . ")");
            }

            return $exist;
        }

        public function addTimestamp() {
            $sql = "INSERT INTO Timestamps (ts) VALUES (now())";

            try {
                $this->_conn->exec($sql);
            } catch (PDOException $e) {
                die("Error: Couldn't add new timestamp (Cause: " . $e->getMessage() . ")");
            }
        }

        public function getLastTimestamp() {
            $sql = "SELECT * FROM Timestamps WHERE id = (SELECT MAX(id) FROM Timestamps)";

            try {
                $stmt = $this->_conn->prepare($sql);
                $stmt->execute();
                return $stmt->fetch();
            } catch (PDOException $e) {
                die("Error: Couldn't add new timestamp (Cause: " . $e->getMessage() . ")");
            }
        }

        public function addCurrency($charCode, $nominal, $name, $value) {
            $sql = "INSERT INTO Currencies
                (char_code, nominal, name, value, ts_id)
                VALUES (:char_code, :nominal, :name, :value, (SELECT MAX(id) FROM Timestamps))";

            try {
                $stmt = $this->_conn->prepare($sql);

                $stmt->bindParam(":char_code", $charCode, PDO::PARAM_STR);
                $stmt->bindParam(":nominal", $nominal, PDO::PARAM_INT);
                $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                $stmt->bindParam(":value", $value, PDO::PARAM_STR);

                $stmt->execute();
            } catch (PDOException $e) {
                die("Error: Couldn't add new currency (Cause: " . $e->getMessage() . ")");
            }
        }

        public function getLastCurrencies() {
            $sql = "SELECT char_code, nominal, name, value, ts_id FROM Currencies
                INNER JOIN Timestamps ON Currencies.ts_id = Timestamps.id
                WHERE Timestamps.id = (SELECT MAX(id) FROM Timestamps)
            ";

            try {
                $stmt = $this->_conn->prepare($sql);
                $stmt->execute();

                $idx = 1;
                while ($row = $stmt->fetch()) {
                    $charCode = $row["char_code"];
                    $nominal  = $row["nominal"];
                    $name     = $row["name"];
                    $value    = $row["value"];

                    echo "<tr>";
                    echo "<th scope=\"row\">$idx</th>";
                    echo "<td>$name</td>";
                    echo "<td>$nominal</td>";
                    echo "<td>$charCode</td>";
                    echo "<td>$value</td>";
                    echo "</tr>";
                    $idx += 1;
                }
            } catch (PDOException $e) {
                die("Error: Couldn't add new currency (Cause: " . $e->getMessage() . ")");
            }
        }

        public function getLastCurrenciesFlipped() {
            $sql = "SELECT char_code, nominal, name, value, ts_id FROM Currencies
                INNER JOIN Timestamps ON Currencies.ts_id = Timestamps.id
                WHERE Timestamps.id = (SELECT MAX(id) FROM Timestamps)
            ";

            try {
                $stmt = $this->_conn->prepare($sql);
                $stmt->execute();

                $idx = 1;
                while ($row = $stmt->fetch()) {
                    $charCode = $row["char_code"];
                    $nominal  = $row["nominal"];
                    $name     = $row["name"];
                    $value    = $row["value"];

                    $nominalFlipped = floatval($nominal) / floatval($value);
                    $valueFlipped = $nominal;

                    echo "<tr>";
                    echo "<th scope=\"row\">$idx</th>";
                    echo "<td>$name</td>";
                    printf("<td>%.4f</td>", $nominalFlipped);
                    echo "<td>$charCode</td>";
                    echo "<td>$valueFlipped</td>";
                    echo "</tr>";
                    $idx += 1;
                }
            } catch (PDOException $e) {
                die("Error: Couldn't add new currency (Cause: " . $e->getMessage() . ")");
            }
        }

        public function close() {
            unset($this->_conn);
        }
    }
?>