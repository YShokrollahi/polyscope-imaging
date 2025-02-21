/*
*   Description: Test cases for the Genomics Table functions
*   Author: Sebastian Schmittner (stp.schmittner@gmail.com)
*   Date: 2015.10.13 13:46
*   LastAuthor: Sebastian Schmittner (stp.schmittner@gmail.com)
*   LastDate: 2015.10.13 13:46
*   Version: 0.0.1
*/

// Depends on QUnit

QUnit.module( "genomicsTable");

QUnit.test("Test isNumeric", function( assert ) {
	assert.ok(isNumeric('123'), '123 is a number');
	assert.ok(isNumeric('123.3'), '123.3 is a number');
	assert.ok(isNumeric('0.123'), '0.123 is a number');
	assert.ok(isNumeric('1e100'), '1e100 is a number');

	assert.ok(!isNumeric('123a'), '123a is NOT a number');
	//assert.ok(!isNumeric(''), '"" is NOT a number');
	assert.ok(!isNumeric('10px'), '10px is NOT a number');
});

QUnit.test("Filter Content Text", function( assert ) {
	assert.equal(filterContent('Test', 0), 'Test', 'Test does NOT change');
	assert.equal(filterContent('"Test"', 0), 'Test', '"Test" gets corrected to Test');
	assert.equal(filterContent('"Test" ', 0), '"Test" ', '"Test"_ does NOT change');
	assert.equal(filterContent(' "Test"', 0), ' "Test"', '_"Test" does NOT change');
	assert.equal(filterContent('"Test', 0), '"Test', '"Test does NOT change');
});

QUnit.test("Filter Content Table", function( assert ) {
	var expected = [[32.45, 87.32], [64.96], 12.33];
	var test = filterTable([['32.4458', '87.3168'], ['64.9648'], '12.3341'], 2);
	
	assert.deepEqual(test, expected, 'Array gets filtered');
});

QUnit.test("Filter Content Numbers", function( assert ) {
	assert.equal(filterContent('100', 0), 100, '100 (0d) does NOT change');
	assert.equal(filterContent('10.5', 0), 11, '10.5 (0d) gets rounded UP');
	assert.equal(filterContent('10.49', 0), 10, '10.49 (0d) gets rounded DOWN');
	assert.equal(filterContent('10.49', 1), 10.5, '10.5 (1d) gets rounded UP');
	assert.equal(filterContent('10.49', 2), 10.49, '10.49 (2d) does NOT change');
	assert.equal(filterContent('1.2316464', 2), 1.23, '1.2316464 (2d) gets rounded DOWN');
	assert.equal(filterContent('2.8897', 2), 2.89, '2.8897 (2d) get rounded UP');
});
