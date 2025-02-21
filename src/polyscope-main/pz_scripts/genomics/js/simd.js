/*
*	Description: SIMD functions
*   Author: Sebastian Schmittner (stp.schmittner@gmail.com)
*   Date: 2015.11.06
*   LastAuthor: Sebastian Schmittner (stp.schmittner@gmail.com)
*   LastDate: 2015.11.06
*   Version: 0.0.1
*/

// Not really SIMD but to make the processing off
// vectors and matrices easier

function transposeLineMatrix(matrix) {
	var rowCount = matrix.length;
	var colCount = matrix[0].length;
	var matrixOut = [];
	
	for(var x = 0; x < colCount; ++x) {
		var newVec = [];
		for(var y = 0; y < rowCount; ++y) {
			newVec.push(matrix[y][x]);
		}
		matrixOut.push(newVec);
	}
	
	return matrixOut;
}

function _min(vector) {
	return Math.min.apply(null, vector);
}

function _max(vector) {
	return Math.max.apply(null, vector);
}

function _floor(vector) {
	for(var i = 0; i < vector.length; ++i) {
		vector[i] = Math.floor(vector[i]);
	}
	return vector;
}

function applyVectorOperation(v1, v2, op) {
	var v3 = [];
	
	if(v2 instanceof Array) {
		for(var i = 0; i < v1.length; ++i) {
			v3.push(op(v1[i], v2[i]));
		}
	}
	else {
		for(var i = 0; i < v1.length; ++i) {
			v3.push(op(v1[i], v2));
		}
	}
	
	return v3;
}

function applyUnaryVectorOperation(v1, op) {
	var v2 = [];
	
	for(var i = 0; i < v1.length; ++i) {
		v2.push(op(v1[i]));
	}
	
	return v2;
}

function applyOnMatrix(matrix, f) {
	var _vals = [];
	for(var i = 0; i < matrix.length; ++i) {
		_vals.push(f(matrix[i]));
	}
	return _vals;
}

function matrixMin(matrix) {
	return applyOnMatrix(matrix, _min);
}

function matrixMax(matrix) {
	return applyOnMatrix(matrix, _max);
}

function _add(v1, v2) {
	return applyVectorOperation(v1, v2, function(a,b) {return a + b;});
}

function _sub(v1, v2) {
	return applyVectorOperation(v1, v2, function(a,b) {return a - b;});
}

function _mul(v1, v2) {
	return applyVectorOperation(v1, v2, function(a,b) {return a * b;});
}

function _div(v1, v2) {
	return applyVectorOperation(v1, v2, function(a,b) {return a / b;});
}

function applyVectorOperationOnMatrix(matrix, vector, f) {
	var _vals = [];
	for(var i = 0; i < matrix.length; ++i) {
		_vals.push(f(matrix[i], vector[i]));
	}
	return _vals;
}

function _matrixSub(matrix, vector) {
	return applyVectorOperationOnMatrix(matrix, vector, _sub);
}

function _matrixDiv(matrix, vector) {
	return applyVectorOperationOnMatrix(matrix, vector, _div);
}

function _matrixAdd(matrix, vector) {
	return applyVectorOperationOnMatrix(matrix, vector, _add);
}

function _matrixMul(matrix, vector) {
	return applyVectorOperationOnMatrix(matrix, vector, _mul);
}
