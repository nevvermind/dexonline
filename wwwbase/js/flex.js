function updateModelTypeList(locVersionSelect, modelTypeListId) {
  var value = locVersionSelect.options[locVersionSelect.selectedIndex].value;
  url = 'ajax/getModelTypesForLocVersion.php?locVersion=' + value;
  if (window.location.toString().indexOf('/admin/') != -1) {
    url = '../' + url;
  }
  makeGetRequest(url, populateModelTypeList, modelTypeListId);
  return false;
}

function updateModelListWithLocVersion(mtSelect, modelListId) {
  var lvSelect = document.getElementById('locVersionListId');
  var lv = lvSelect.options[lvSelect.selectedIndex].value;
  var mt = mtSelect.options[mtSelect.selectedIndex].value;
  url = 'ajax/getModelsForLocVersionModelType.php' +
    '?locVersion=' + lv +
    '&modelType=' + mt;
  makeGetRequest(url, populateModelList, [true, modelListId]);
  return false;
}

function updateModelList(modelTypeSelect, modelListId) {
  var value = modelTypeSelect.options[modelTypeSelect.selectedIndex].value;
  makeGetRequest('../ajax/getModelsForLocVersionModelType.php?modelType=' +
                 value, populateModelList, [false, modelListId]);
  return false;
}

function populateModelTypeList(httpRequest, modelTypeListId) {
  if (httpRequest.readyState == 4) {
    if (httpRequest.status == 200) {
      var select = document.getElementById(modelTypeListId);
      select.options.length = 0;

      result = httpRequest.responseText;
      var lines = result.split('\n');

      for (var i = 0; i < lines.length && lines[i]; i += 2) {
        var val = lines[i];
        var descr = lines[i + 1];
        var display = val + ' (' + descr + ')';
        select.options[select.options.length] = new Option(display, val);
      }

      // Now update the model list since the model type list has changed.
      var mtSelect = document.getElementById('modelTypeListId');
      updateModelListWithLocVersion(mtSelect, 'modelListId');
    } else {
      alert('Nu pot descărca lista de tipuri de modele!');
    }
  }
}

function populateModelList(httpRequest, argArray) {
  var createExtraOption = argArray[0];
  var modelListId = argArray[1];
  if (httpRequest.readyState == 4) {
    if (httpRequest.status == 200) {
      var select = document.getElementById(modelListId);
      select.options.length = 0;

      if (createExtraOption) {
        select.options[0] = new Option("Toate", -1);
      }

      result = httpRequest.responseText;
      var lines = result.split('\n');

      for (var i = 0; i < lines.length && lines[i]; i += 3) {
        var id = lines[i];
        var number = lines[i + 1];
        var exponent = lines[i + 2];
        var display = number + (id == '0' ? '*' : '') + ' (' + exponent + ')';
        select.options[select.options.length] = new Option(display, number);
      }
    } else {
      alert('Nu pot descărca lista de exponenţi pentru modele!');
    }
  }
}

function blUpdateParadigmVisibility(radioButton) {
  // Locate the div containing one sub-div for each paradigm
  var components = radioButton.name.split('_', 2);
  var lexemId = components[1];
  var modelId = radioButton.value;
  var parentDiv = document.getElementById('paradigms_' + lexemId);

  var expectedDivId = 'paradigm_' + lexemId + '_' + modelId;
  for (var i = 0; i < parentDiv.childNodes.length; i++) {
    var div = parentDiv.childNodes[i];
    if (div.nodeName == 'DIV') {
      div.style.display = (div.id == expectedDivId) ? 'block' : 'none';
    }
  }

  return true;
}

function blUpdateDefVisibility(anchor) {
  var components = anchor.id.split('_', 2);
  var lexemId = components[1];
  var div = document.getElementById('definitions_' + lexemId);
  if (div.style.display == 'none') {
    div.style.display = 'block';
    anchor.innerHTML = 'ascunde definiţiile';
  } else {
    div.style.display = 'none';
    anchor.innerHTML = 'arată definiţiile';
  }
  return false;
}

function showDiv(divId) {
  var div = document.getElementById(divId);
  div.style.display = 'block';
}

function hideDiv(divId) {
  var div = document.getElementById(divId);
  div.style.display = 'none';
}

function apSelectLetter(lexemId, cIndex) {
  if (cIndex != -1) {
    var span = document.getElementById('letter_' + lexemId + '_' + cIndex);
    span.innerHTML = "'" + span.innerHTML;
    var checkbox = document.getElementById('noAccent_' + lexemId);
    checkbox.checked = false;
  }

  var input = document.getElementById('position_' + lexemId);

  if (input.value != -1) {
    var oldSpan = document.getElementById('letter_' + lexemId + '_' +
                                          input.value);
    oldSpan.innerHTML = oldSpan.innerHTML.substring(1);
  }
  input.value = cIndex;
}