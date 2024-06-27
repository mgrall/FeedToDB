# FeedToDB Project

**FeedToDB** is a PHP-based application designed to parse XML feeds and store their content into a SQLite database.

**Assignment:** Create a command-line program that processes a local XML file (feed.xml) and pushes data of that XML to a DB of your choice.

<br>

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

<br>

## How to Run
### Run the application
```bash
php main.php
```
This will start the process of reading the XML feed and inserting data into the SQLite database.<br>
Keep in mind that I did not set a PRIMARY KEY for testing, so any subsequent runs will add more items to the database.

setup.php will attempt to create the missing directories for the application environment (included in main.php).

<br>

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

<br>

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

<br>

## How to extend the application

### How to add additional databases, data sources or loggers
Create a class that implements **DatabaseInterface**, **ParserInterface** or **LoggerInterface**.

Add the configuration parameters to **Config/config.php**

Add the new class as an option to **src/Factory.php**, which allows us to easily choose between any Interface implementation based on use case.

### Want to keep database, parser and logger implementations but change their interaction?
Create a class that implements **FactoryInterface**.

<br>

## Initial Concept and Design
The foundation of the project was the implementation of interfaces for managing various data sources and databases. My first design approach was to pass configuration settings through config.php to a class that dynamically selects the appropriate DataSource or Database class based on the contents of config.php. This setup ensures that all configuration parameters are consolidated in a handy file, while avoiding mixing business logic with configuration settings.

### The Factory
Inspired by the factory method pattern, Factory.php was developed to instantiate DataSource and Database objects as specified in config.php. This includes setting parameters like data type (XML, JSON, etc.) and database connections (port, dbname, host). I also contemplated introducing a Factory Interface to allow customization in how data is imported or logged, supporting the extension of our factory mechanism by users who might want to inject additional logic.

A interesting design decision was whether to create multiple factories, such as DataBaseFactory, DataSourceFactory, and LoggerFactory, or to consolidate these into a single Factory. I opted for a single Factory to keep the system simple and more manageable, avoiding unnecessary complexity in our project structure.

Another interesting question was whether to pass all required arguments in the constructor or to pass them through the import function when needed. In the end, I decided to focus on using the constructor since it made our main.php less prone to implementation failures. This approach essentially helped avoid the following scenario:
```php
// Construct our handler with specific interface implementations for Parser, DB and Logger, depending on use case.
$factory = new Factory(new XMLFeedParser(), new PDOConnector(), new FileFeedLogger());

// do some other stuff...

// Forget we initialized Importer with XMLFeedParser and pass a json file instead.
$factory->import(Config::get('database', 'sqlite'), Config::get('data_source', 'JSON_path'));
```
revised:
```php
// Construct our handler with specific interface implementations for Parser, DB and Logger, depending on use case.
$factory= new Factory(
	new XMLFeedParser(Config::get('data_source', 'xml_path')),
	new PDOConnector(Config::get('database', 'sqlite')),
	new FileFeedLogger()
);
// Removes the potential point of failure here
$factory->import();
```

Eventually I decided to completely decouple any object creation (outside of Factory) from main.php <br>
new:
```php
$factory = new Factory(
        Config::get('database', 'sqlite01'),
        Config::get('data_source', 'feed.xml'),
        Config::get('logger', 'logger01')
    );
$factory->importFeed();
```



### Config
The configuration handling was implemented as a class  to accommodate potential complexity and growth in settings as the project evolves. This class-based approach provides a structured way to manage configurations robustly.

### About Logging
I decided to implemented the logger in a similar fashion to how I managed our database and data source interfaces. The only difference being, instead of crafting a custom interface, I chose to integrate the widely recognized PSR-3 Standard LoggerInterface.

### About Data Sources
Different data formats, such as XML or JSON, may require specific parsing strategies, especially if we encounter structured complexities like nested objects or specific attributes. While the current example does not utilize these complex structures, the flexibility to handle them in the future may be needed.
If our XML file isn't as neatly structured as the test data, we'll need to flatten it before inserting it into a relational database. Additionally, we'll require a standardized method to serialize any filetype into a readable object. 
In order to avoid these problems, I have decided to declare the desired schema in config.php, as it appears that knowing the schema beforehand is the expected use case for this application. Automatically determining the schema would necessitate a more extensive implementation of the ParserInterface, which remains feasible within the context of our system design.

### About Databases
Each database interacts with our system via a specific connection and insertion process. By leveraging the PDO Interface, we can standardize these interactions for a wide range of relational databases. This leaves out NoSQL databases, which can be accommodated by implementing DatabaseInterface in a custom class such as MongoDBConnector. 
