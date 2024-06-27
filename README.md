# FeedToDB Project

**FeedToDB** is a PHP-based application designed to parse XML feeds and store their content into a SQLite database.

**Assignment:** Create a command-line program that processes a local XML file (feed.xml) and pushes data of that XML to a DB of your choice.

## How to Setup

### Prerequisites
- **PHP 8.0** or higher
- **Composer** for managing PHP dependencies
- **pdo_sqlite** enabled

### Installation
1. **Clone the repository**
```bash
git clone https://github.com/mgrall/FeedToDB.git
```
2. **Install dependencies**
```bash
cd FeedToDB
composer install
```

### Extensions
Make sure to enable **extension=pdo_sqlite** in your php.ini

## How to Run
### Run the application
```bash
php main.php
```
This will start the process of reading the XML feed and inserting data into the SQLite database.<br>
Keep in mind that I did not set a PRIMARY KEY for testing, so any subsequent runs will add more items to the database.

## Project Structure
```bash
FeedToDB
├── config
│   └── Config.php
├── data
│   └── feed.xml
├── src
│   ├── Database
│   │   ├── DatabaseInterface.php
│   │   └── PDOConnector.php
│   ├── Logger
│   │   └── FileFeedLogger.php
│   └── Parser
│   │   ├── ParserInterface.php
│   │   ├── XMLFeedParser.php
│   ├── Factory.php
│   └── FactoryInterface.php
├── storage
│   ├── database
│   │   ├── data.db
│   │   └── data.sqlite
│   └── logs
│       └── app.log
├── tests
│   ├── ImporterTest.php
│   ├── PDOConnectorTest.php
│   └── XMLFeedParserTest.php
├── vendor
├── composer.json
├── composer.lock
├── setup.php
└── main.php
```

## System Design
The system is designed with modularity and extendability in mind.<br>
It utilizes interfaces for the Database (**DatabaseInterface**), DataSource (**ParserInterface**) and Logger (**LoggerInterface**), allowing to easily extend the applications with further implementation.<br>
![Screenshot 2024-06-27 054732](https://github.com/mgrall/FeedToDB/assets/23291884/895c2251-6b05-446f-b64e-14dcee28ea6d)


**DatabaseInterface:** An interface that facilitates inserting data into databases.<br>
**PDOConnector:** Connects with relational Databases whose drivers implement the PDO interface (SQLITE, MySQL, IBM, etc.)

**ParserInterface:** An interface that facilitates parsing data sources into readable objects.<br>
**XMLFeedParser:** Parses XML files, utilizing SimpleXML.

**LoggerInterface:** PSR-3: Logger Interface.<br>
**FileFeedLogger:** A simple logger implementation compliant with the PSR-3 Logger Interface.

**FactoryInterface:** An interface that connects Parser, Database and Logger.<br>
**Factory:** Uses config settings to create and control the desired implementations for DatabaseInterface, ParserInterface and LoggerInterface. Main.php uses this class to start the insertion process.

**Main:** Can be replaced with a CLI of your choice. Only uses Factory and Config. 
