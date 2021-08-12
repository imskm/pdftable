# A PDF Generator

## Create object
```php
use PDFTable\PDFTable;

$pdftable = new PDFTable();
```

## Declare columns
```php
$pdftable->addColumn("Column Name");
```

## Add records
```php
$pdftable = new PDFTable();

$data = [
	[1, "Customer #1", "9330000001", 4000, 400],
	[2, "Customer #2", "9330000002", 40000, 15500],
	[3, "Customer #3", "9330000002", 40000, 15500],
];
$logo = __DIR__ . '/logo.png';

// 1. Set header info - Company details
$pdftable->setLogo($logo);
$pdftable->setFrom([
	"Company Name",
	// Add more lines by passing each line as seperate argument
]);

// 2. Add columns
$pdftable->addColumn("SL/No.");
$pdftable->addColumn("Customer");
$pdftable->addColumn("Phone");
$pdftable->addColumn("Principal");
$pdftable->addColumn("Interest");

// 3. Add data in rows
foreach ($rows as $row) {
	$pdftable->addRecord($row);
}

$pdftable->render("output.pdf", "F");
```

