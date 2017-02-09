# Validation and Sanitation

```
CRUD::sanitize(mixed $var, array $against = []);
```

|param|description|
|---|---|
|$var|The variable to sanitize/validate|
|$against|Array of data to validate and sanitize the variable against|

__RETURNS__ the sanitized/validated variable that was given
__THROWS__ an exception if there was any issues with the validation

## Basic

```
CRUD::sanitize('string');
```

## Email

```
CRUD::sanitize('john@smith.com', ['email']);
```

## Strings

```
CRUD::sanitize('string', ['string']); // is the given var a valid string?
CRUD::sanitize('string', ['string', 'strlen' => ['short' => 3]]); // is the given var at least 3 characters long?
CRUD::sanitize('string', ['string', 'strlen' => ['long' => 10]]) // does the given var have less than 10 characters?

CRUD::sanitize('<b>string</b>', ['string', 'notags']);
CRUD::sanitize('string', ['string', 'match' => 'a-z']); // is the given var an alphanumeric string?
CRUD::sanitize('string_', ['string', 'match' => '_']); // is the given var an alphanumeric string with the exception of the underscore?

CRUD::sanitize('password', ['string', 'password']); // returns a hashed password
```

## Numbers

```
CRUD::sanitize(1, ['number']);
```

## Booleans

```
CRUD::sanitize(true, ['boolean']);
```

## Required Fields

```
CRUD::sanitize('something', ['required']);
CRUD::sanitize('John Smith', ['name', 'required-full']); // looks for a space (first, last name)
```

## Dates

```
CRUD::sanitize('2017-01-01', ['date']); // is the given var a date?
CRUD::sanitize('2017-01-01', ['date', 'date_format' => 'm/d/Y']); // if it's a date, give it back like this
```

## Names

```
CRUD::sanitize('John', ['name']); // returns ['first' => 'John', 'last' => ''];
CRUD::sanitize('John Smith', ['name']); // returns ['first' => 'John', 'last' => 'Smith'];
CRUD::sanitize('John le'Smith', ['name']); // returns ['first' => 'John', 'last' => 'le\'Smith'];
CRUD::sanitize('John <b>Smith</b>', ['name']); // returns ['first' => 'John', 'last' => 'Smith'];
```

# Data

## Compiling
Turns data into db usable stuff

```
CRUD::compile(['table column name' => 'row value']);
```
