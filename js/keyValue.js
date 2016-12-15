/*
  KeyValue
  Version: 1.2

  Albert Cansado Sola
  @albertcansado
*/
var KeyValue = (function () {

	"use strict";

	var defaultOptions = {
		table: null,
		btn: null,
		inputLabel: null,
		inputValue: null,
		hiddenInput: null,
        checkTitle: null,
		saveOrder: null,
		empty: true,
		emptyText: 'No items to display',
		edit: {
			editing: false,
			deleted: false,
			row: null,
			beforeInput1: '',
			beforeInput2: ''
		},
        formatText: '%s1:%s2:%s3',
		separator: ';',
        formatId: 'kv%s1',
        nextId: 1,
		inputs: {
			name: 'm1_',
			checkboxName: 'multiselect[]'
		},
		lang: {
			'edit': 'Edit',
			'saveRow': 'SaveRow',
			'delete': 'Delete'
		},
		icons: {
			deleteIcon: 'themes/OneEleven/images/icons/system/delete.gif',
			editIcon: 'themes/OneEleven/images/icons/system/edit.gif',
			cancelIcon: 'themes/OneEleven/images/icons/extra/false.gif',
			saveIcon: 'themes/OneEleven/images/icons/extra/green.gif'
		},
		rowClass: {
			odd: 'row1',
			even: 'row2',
			actual: 'odd'
		}
	},

	utils = {
		extend: function (obj) {
			if (!this.isObject(obj)) {
				return obj;
			}
			var source, prop;
			for (var i = 1, length = arguments.length; i < length; i++) {
				source = arguments[i];
				for (prop in source) {
					obj[prop] = source[prop];
				}
			}
			return obj;
		},
		insert: function (el, foo) {
			el.innerHTML = '';
			if (this.isElement(foo)) {
				el.appendChild(foo);
			} else {
				el.innerHTML = foo.toString();
			}
			return el;
		},
		getValue: function (el) {
			if (!el || el.tagName.toLowerCase() !== 'input') {
				return '';
			}
			return el.value.toString().trim();
		},
		setValue: function (el, value) {
			if (!el || el.tagName.toLowerCase() !== 'input') {
				return '';
			}
			el.value = value;
			return el;
		},
		isUndefined: function (foo) {
			return typeof foo === 'undefined';
		},
		isNull: function (foo) {
			return Boolean(foo === null);
		},
		isEmpty: function (str) {
			return (!str || str.length === 0);
		},
		isElement: function (foo) {
			return foo instanceof Element;
		},
		isBoolean: function (foo) {
			return typeof foo === 'boolean';
		},
		isFunction: function (foo) {
			return typeof foo === 'function';
		},
		isObject: function (obj) {
			var type = typeof obj;
			return type === 'function' || type === 'object' && !! obj;
		},
		equalTag: function (el, tagName) {
			try {
				return el.tagName.toLowerCase() === tagName.toString();
			} catch (err) {
				return false;
			}
		},
		format: function (pattern) {
			if (arguments.length <= 1 || this.isUndefined(pattern)) {
				return false;
			}
			var text = pattern;
			for (var i = 1, j = arguments.length; i < j; i++) {
				var toFind = '%s' + i.toString();
				text = text.replace(toFind, encodeURIComponent(arguments[i]));
			}
			return text;
		},
		findParentElement: function (el, name) {
			if (this.isUndefined(el) || this.isEmpty(name)) {
				throw 'params are required';
			}
			var found = this.equalTag(el, name),
			limit = false,
			max = 5,
			num = 0;
			while (!found && !limit) {
				el = el.parentNode;
				found = this.equalTag(el, name);
				num++;
				limit = (num === max) ? true : false;
			}
			if (limit) {
				return false;
			}
			return el;
		},
		getRow: function (el) {
			if (this.isUndefined(el)) {
				throw 'el is undefined';
			}
			if (!this.isElement(el)) {
				throw 'is not a valid DOM Element';
			}
			return this.findParentElement(el, 'tr');
		},
		getCol: function (el, index) {
			if (this.isUndefined(el) || !this.isElement(el)) {
				throw 'empty or invalid element';
			}
			if (this.equalTag(el, 'tr')) {
            //Use index
            return el.cells[parseInt(index, 10)];
        } else {
            //While
            return this.findParentElement(el, 'td');
        }
    },
    getColValue: function (el, index) {
    	if (this.isUndefined(el)) {
    		throw 'element is required';
    	}
    	if (this.isEmpty(index)) {
    		index = 0;
    	}
    	var td = this.getCol(el, index);
    	if (td.hasChildNodes() && this.equalTag(td.childNodes[0], 'input')) {
    		return td.childNodes[0].value;
    	} else {
    		return td.innerHTML;
    	}
    }
};

var KeyValue = function () {
	this.options = utils.extend({}, defaultOptions, arguments[0]);
	if (utils.equalTag(this.options.table, 'table')) {
		this.options.table = this.options.table.tBodies[0];
	}
	this.init();
};

KeyValue.prototype.init = function () {
	if (this.options.btn) {
		this.options.btn.onclick = this.onClickSave.bind(this);
	}
	if (this.options.saveOrder) {
		this.options.saveOrder.onclick = this.onClickSaveOrder.bind(this);
	}
        if (this.options.checkTitle) {
          this.options.checkTitle.onclick = this.onClickCheckBox.bind(this);
        }
	var _numRows = this.options.table.rows.length;
	this.options.empty = (_numRows === 0);
	this.options.nextId = _numRows + 1;
	if (this.options.empty) {
		this._addEmptyRow();
	} else {
		var editList = document.querySelectorAll('.kv-edit'),
		deleteList = document.querySelectorAll('.kv-delete'),
		editListl = editList.length,
		deleteListl = deleteList.length,
		maxElem = Math.max(editListl, deleteListl);
		for (var i = 0, j = maxElem; i < j; i++) {
			if (i < editListl) {
				editList[i].onclick = this.onClickEdit.bind(this);
			}
			if (i < deleteListl) {
				deleteList[i].onclick = this.onClickDelete.bind(this);
			}
		}
	}
};

KeyValue.prototype._addEmptyRow = function () {
	var tr = this.options.table.insertRow(-1);
	tr.className = 'kv-empty';
	var td = tr.insertCell(0);
	td.innerHTML = this.options.emptyText;
	td.colSpan = "5";
};

KeyValue.prototype._setId = function() {
    var valId = utils.format(this.options.formatId, this.options.nextId);
    this.options.nextId += 1;
    return valId;
};

KeyValue.prototype._edited = function () {
	if (arguments.length && !utils.isUndefined(arguments[0]))  {
		this.options.edit.editing = Boolean(arguments[0]);
	}
	return this.options.edit.editing;
};

KeyValue.prototype._editDelete = function () {
	if (arguments.length && !utils.isUndefined(arguments[0]))  {
		this.options.edit.deleted = Boolean(arguments[0]);
	}
	return this.options.edit.deleted;
};

KeyValue.prototype._rowEditing = function () {
	if (arguments.length && !utils.isUndefined(arguments[0]))  {
		this.options.edit.row = arguments[0];
	}
	return this.options.edit.row;
};

KeyValue.prototype._editingValue = function (field, value) {
	if (!this.options.edit.hasOwnProperty(field)) {
		return false;
	}
	if (!utils.isUndefined(value)) {
		this.options.edit[field] = value.toString();
	}
	return this.options.edit[field];
};

KeyValue.prototype._createCheckBox = function () {
	var check = document.createElement('input');
	check.type = 'checkbox';
	check.className = 'multiselect';
	check.name = this.options.inputs.name + this.options.inputs.checkboxName;
	return check;
};

KeyValue.prototype._createEditBtn = function () {
	var edit = document.createElement('a');
	edit.className = 'kv-edit';
	edit.onclick = this.onClickEdit.bind(this);
	edit.innerHTML = '<img src="' + this.options.icons.editIcon + '" alt="Edit">';
	edit.href = '#';
	return edit;
};

KeyValue.prototype._createCancelEditBtn = function () {
	var cancel = document.createElement('a');
	cancel.className = 'kv-cancel';
	cancel.onclick = this.onClickCancelEdit.bind(this);
	cancel.innerHTML = '<img src="' + this.options.icons.cancelIcon + '" alt="Cancel">';
	cancel.href = '#';
	return cancel;
};

KeyValue.prototype._createSaveBtn = function () {
	var a = document.createElement('a');
	a.className = 'kv-save';
	a.onclick = this.onSaveRow.bind(this);
	a.innerHTML = '<img src="' + this.options.icons.saveIcon + '" alt="Save">';
	a.href = '#';
	return a;
};

KeyValue.prototype._createDeleteBtn = function () {
	var a = document.createElement('a');
	a.className = 'kv-delete';
	a.onclick = this.onClickDelete.bind(this);
	a.innerHTML = '<img src="' + this.options.icons.deleteIcon + '" alt="Delete">';
	a.href = '#';
	return a;
};

KeyValue.prototype._createInput = function (el, value) {
	var input = document.createElement('input');
	input.type = 'text';
	input.value = value;
	input.className = 'kv-input';
	utils.insert(el, input);
	return input;
};

/** Inline **/
KeyValue.prototype._createInlineEdit = function () {
	var tr = this._rowEditing();
	var col1 = utils.getCol(tr, 0);
	var col2 = utils.getCol(tr, 1);

    //S'ha de guardar els valors inicials per cancelar o per despres borrar.
    var inputVal1 = utils.getColValue(col1);
    var inputVal2 = utils.getColValue(col2);

    this._editingValue('beforeInput1', inputVal1);
    this._editingValue('beforeInput2', inputVal2);

    this._createInput(col1, inputVal1);
   	this._createInput(col2, inputVal2);
};

KeyValue.prototype._restoreInlineEdit = function () {
	utils.insert(utils.getCol(this._rowEditing(), 3), this._createEditBtn());
	this._rowEditing(null);
	this._editingValue('beforeInput1', '');
	this._editingValue('beforeInput2', '');
	/*this._editDelete(false);*/
	this._edited(false);
};

KeyValue.prototype._rollbackInlineEdit = function () {
	var tr = this._rowEditing();

    //Eliminem Inputs i posem valors inicials.
    var col1 = utils.getCol(tr, 0);
    var col2 = utils.getCol(tr, 1);

    var oldlabel = this._editingValue('beforeInput1');
    var oldValue = this._editingValue('beforeInput2');

    utils.insert(col1, oldlabel);
    utils.insert(col2, oldValue);

    //utils.insert(utils.getCol(tr, 3), this._createEditBtn());
    /*if (this._editDelete()) {
    	this.updateHidden(oldlabel, oldValue);
    }*/

    this._restoreInlineEdit();
};

/*KeyValue.prototype._removeOldInline = function () {
    var tr = this._rowEditing();

    //Remove Old Value
    this.updateHidden(this._editingValue('beforeInput1'), this._editingValue('beforeInput2'), true);

    this._editDelete(true);

    return true;
};*/

KeyValue.prototype._updateRow = function () {
	var tr = this._rowEditing();

	var col1 = utils.getCol(tr, 0);
	var col2 = utils.getCol(tr, 1);

	var trId = tr.dataset.id;

    //Get New Values
    var label = utils.getColValue(col1);
    var value = utils.getColValue(col2);
    var toSave = utils.format(this.options.formatText, trId, label, value);

    //Get Old values
    var oldValue = utils.format(this.options.formatText, trId, this._editingValue('beforeInput1'), this._editingValue('beforeInput2'));

    //Update Hidden
    this.options.hiddenInput.value = this.options.hiddenInput.value.replace(oldValue, toSave);

    utils.insert(col1, label);
    utils.insert(col2, value);

    return true;
};
/** --- **/

KeyValue.prototype.getRowValue = function (tr) {
	if (utils.isUndefined(tr)) {
		throw 'row is undefined';
	}
	return utils.format(this.options.formatText, tr.dataset.id, utils.getColValue(tr, 0), utils.getColValue(tr, 1));
};

KeyValue.prototype.isEmptyFields = function () {
	return (this.options.inputLabel.value === '' || this.options.inputValue.value === '');
};

KeyValue.prototype.updateHidden = function () {
	var remove = false;
	var text_input = '';
	if (arguments.length === 1) {
        //Value Formated
        text_input = arguments[0];
    } else if (arguments.length === 2) {
        //text formated and remove
        if (utils.isBoolean(arguments[1])) {
        	text_input = arguments[0];
        	remove = arguments[1];
        } else {
            //label, value
            text_input = utils.format(this.options.formatText, arguments[0], arguments[1]);
        }
    } else {
        //label, value, id, remove
        text_input = utils.format(this.options.formatText, arguments[0], arguments[1], arguments[2]);
        if (arguments.hasOwnProperty(3)) {
            remove = Boolean(arguments[3]);
        }
    }

    var hidd_val = utils.getValue(this.options.hiddenInput),
    newText = hidd_val;

    if (remove) {
    	var re = new RegExp(text_input + "[;]?|[;]?" + text_input + "");
    	newText = hidd_val.replace(re, '');
    } else {
    	newText = hidd_val;
    	if (!utils.isEmpty(hidd_val)) {
    		newText += this.options.separator + text_input;
    	} else {
    		newText = text_input;
    	}
    }

    utils.setValue(this.options.hiddenInput, newText);
};

KeyValue.prototype.clearFields = function () {
	this.options.inputLabel.value = '';
	this.options.inputValue.value = '';
};

KeyValue.prototype.onClickSave = function (event) {
	event.preventDefault();
	if (this.isEmptyFields()) {
		return;
	}
	if (this._edited()) {
		this._rollbackInlineEdit();
	}
	if (!this.addRow()) {
		alert('Error adding a Row');
	}
	this.clearFields();
};

KeyValue.prototype.onClickEdit = function (event) {
	event.preventDefault();
	/* Si ja estem editant abortem */
	if (this._edited()) {
		return;
	}
	/* Marquem que estem editant */
	this._edited(true);

	/* Guardem tr */
	this._rowEditing(utils.getRow(event.target));

	/* Creem inputs */
	this._createInlineEdit();

	/* Afegim btn Save & Cancel */
	var td = utils.getCol(event.target);
	utils.insert(td, this._createSaveBtn());
	td.appendChild(this._createCancelEditBtn());
};

/**
 * Callback lanzado cuando queremos guardar la fila que editamos
 */
KeyValue.prototype.onSaveRow = function (event) {
	event.preventDefault();
	if (!this._edited()) {
		return;
	}

	try {
		this._updateRow();

	} catch (err) {
		this._rollbackInlineEdit();
		alert('Error when update values');
	}
	this._restoreInlineEdit();
};

KeyValue.prototype.onClickDelete = function (event) {
	event.preventDefault();
	var tr = utils.getRow(event.target);
	this.removeRow(tr);
};

KeyValue.prototype.onClickCancelEdit = function (event) {
	event.preventDefault();
	this._rollbackInlineEdit();
};

KeyValue.prototype.onClickSaveOrder = function (event) {
	event.preventDefault();
        var newText = '';
        for (var i = 0, j = this.options.table.rows.length; i < j; i++) {
          newText += this.getRowValue(this.options.table.rows[i]);
          newText += (j - i > 1) ? ';' : '';
        }

        utils.setValue(this.options.hiddenInput, newText);
};

KeyValue.prototype.onClickCheckBox = function(event) {
    var isChecked = event.currentTarget.checked;
    this.options.inputValue.value = (isChecked) ? 'TITLE' : '';
    this.options.inputValue.disabled = isChecked;
};

KeyValue.prototype.addCell = function (tr, pos, html) {
	var td = tr.insertCell(pos);
	td.className = 'kv-col' + pos.toString();
	if (utils.isFunction(html)) {
		utils.insert(td, html.apply(this));
	} else {
		utils.insert(td, html);
	}
	return td;
};

KeyValue.prototype.addRow = function () {
	try {
        //first row
        if (this.options.empty) {
        	this.options.table.deleteRow(-1);
        	this.options.empty = false;
        }
        var label = utils.getValue(this.options.inputLabel);
        var value = utils.getValue(this.options.inputValue);

        var tr = this.options.table.insertRow(-1);
        tr.className = this.options.rowClass[this.options.rowClass.actual];
        this.options.rowClass.actual = (this.options.rowClass.actual === 'odd') ? 'even' : 'odd';
        var trId = this._setId();
        tr.dataset.id = trId;

        this.addCell(tr, 0, label);
        this.addCell(tr, 1, value);
        this.addCell(tr, 2, this._createCheckBox);
        this.addCell(tr, 3, this._createEditBtn);
        this.addCell(tr, 4, this._createDeleteBtn);

        this.updateHidden(trId, label, value);

    } catch (err) {
    	return false;
    }
    return true;
};

KeyValue.prototype.removeRow = function (tr) {
	try {
		var rowValue = this.getRowValue(tr);
		this.options.table.deleteRow(tr.rowIndex - 1);
		this.updateHidden(rowValue, true);
		if (this.options.table.rows.length === 0) {
			this.options.empty = true;
			this.options.nextId = 1;
			this._addEmptyRow();
		}
	} catch (err) {
		return false;
	}
	return true;
};

	return KeyValue;
}());
