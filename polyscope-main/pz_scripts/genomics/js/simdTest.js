/*
*	Description: SIMD functions unit tests
*   Author: Sebastian Schmittner (stp.schmittner@gmail.com)
*   Date: 2015.11.06
*   LastAuthor: Sebastian Schmittner (stp.schmittner@gmail.com)
*   LastDate: 2015.11.06
*   Version: 0.0.1
*/

QUnit.module( "simd");

QUnit.test("transposeLineMatrix", function( assert ) {
	var sample = [[1, 2, 3], [4, 5, 6], [7, 8, 9]];
	var expected = [[1, 4, 7], [2, 5, 8], [3, 6, 9]];
	
	assert.deepEqual(transposeLineMatrix(sample), expected, 'line matrix transpose');
});

QUnit.test("_min/_max (vector)", function( assert ) {
	assert.equal(_min([3, 4, 7, 0, 1, 5, 6, 7, 9, 10]), 0, '0 is min');
	assert.equal(_min([7, 5, 6, 7, 9, 10]), 5, '5 is min');
	assert.equal(_min([10]), 10, '10 is min');

	assert.equal(_min([0.5, 2.4, 0.007, 0.230, 0.985]), 0.007, '0.007 is min');
	assert.equal(_min([1.2, 4.6, 0.0, -0.63, -1.35]), -1.35, '-1.35 is min');

	assert.equal(_max([3, 4, 7, 0, 1, 5, 9, 7, 5]), 9, '9 is max');
	assert.equal(_max([3, 4, 7, 0, 7, 5, 6, 0]), 7, '7 is max');
	assert.equal(_max([0, 1]), 1, '1 is max');

	assert.equal(_max([0.5, 2.4, 0.007, 0.230, 0.985]), 2.4, '2.4 is max');
	assert.equal(_max([1.2, 4.6, 0.0, -0.63, -1.35]), 4.6, '4.6 is max');
});

QUnit.test("_floor (vector)", function( assert ) {
	var sample = [1.2, 4.6, 0.0, -0.63, -1.35];
	var expected = [1.0, 4.0, 0.0, -1.0, -2.0];
	
	assert.deepEqual(_floor(sample), expected, 'floor on vector');
});

QUnit.test("applyVectorOperation", function( assert ) {
	var in1 = [1, 4, 0, -1, -3];
	var in2 = [2, 6, 12, 5, -7];
	var in3 = 8;
	
	var fAdd = function(a, b){ return a + b; };
	
	var expected1 = [3, 10, 12, 4, -10];
	var expected2 = [9, 12, 8, 7, 5];
	
	assert.deepEqual(applyVectorOperation(in1, in3, fAdd), expected2, 'vector + scalar');
	assert.deepEqual(applyVectorOperation(in1, in2, fAdd), expected1, 'vector + vector');
});

QUnit.test("applyUnaryVectorOperation", function( assert ) {
	var vector = [1, 4, 0, -1, -3];
	var f = function(x) { return x * 2 };
	var expected = [2, 8, 0, -2, -6];
	
	assert.deepEqual(applyUnaryVectorOperation(vector, f), expected, 'applyUnaryVectorOperation');
});

QUnit.test("applyOnMatrix", function( assert ) {
	var matrix = [[1, 2, 3], [4, 5, 6], [7, 8, 9]];
	var f = _min;
	
	var expected = [1, 4, 7];
	
	assert.deepEqual(applyOnMatrix(matrix, f), expected, 'applyOnMatrix');
});

QUnit.test("applyVectorOperationOnMatrix", function( assert ) {
	var matrix = [[1, 2, 3], [4, 5, 6], [7, 8, 9]];
	var vector = [3, 5, 7];
	var f = _sub;
	
	var expected = [[-2, -1, 0], [-1, 0, 1], [0, 1, 2]];
	
	assert.deepEqual(applyVectorOperationOnMatrix(matrix, vector, f), expected, 'applyVectorOperationOnMatrix');
});
