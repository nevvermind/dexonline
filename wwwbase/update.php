<?
require_once("../phplib/util.php");

// Allow this script to run indefinitely long
set_time_limit(0);

// If no GET arguments are set, print usage and return.
if (count($_GET) == 0) {
  smarty_displayWithoutSkin('common/updateInstructions.ihtml');
  return;
}

$acceptEncoding = isset($_SERVER['HTTP_ACCEPT_ENCODING'])
  ? $_SERVER['HTTP_ACCEPT_ENCODING'] : "";

if (strstr($acceptEncoding, "gzip") === FALSE) {
  header("HTTP/1.0 403 Forbidden");
  return;
}

header('Content-type: text/xml');

$timestamp = util_getRequestIntParameter('timestamp');
$version = util_getRequestParameterWithDefault('version', '1.0');
$defDbResult = Definition::loadByMinModDate($timestamp);
$lexemDbResult = Lexem::loadNamesByMinModDate($timestamp);
$sourceMap = createSourceMap();
userCache_init();
$currentLexem = array(0, ''); // Force loading a lexem on the next comparison.

smarty_assign('defDbResult', $defDbResult);
smarty_assign('numResults', mysql_num_rows($defDbResult));
smarty_assign('currentTimestamp', time());
smarty_assign('version', $version);
smarty_assign('includeNameWithDiacritics', hasFlag('a'));
smarty_displayWithoutSkin('common/update.ihtml');
mysql_free_result($defDbResult);
return;



function createSourceMap() {
  $sourceMap = array();
  $sources = Source::loadAllSources();
  foreach ($sources as $source) {
    $sourceMap[$source->id] = $source;
  }
  return $sourceMap;
}

function userCache_init() {
  $GLOBALS['USER'] = array();
}

function userCache_get($key) {
  if (array_key_exists($key, $GLOBALS['USER'])) {
    return $GLOBALS['USER'][$key];
  }

  $user = User::load($key);
  $GLOBALS['USER'][$key] = $user;
  return $user;
}

function hasFlag($f) {
  return (isset($_GET['flags']) &&
	  strstr($_GET['flags'], $f) !== FALSE);
}

function fetchNextRow() {
  global $defDbResult;
  global $lexemDbResult;
  global $sourceMap;
  global $currentLexem;

  $dbRow = mysql_fetch_assoc($defDbResult);
  $def = Definition::createFromDbRow($dbRow);
  $def->internalRep = text_xmlizeRequired($def->internalRep);
  if (hasFlag('d')) {
    $def->internalRep = text_xmlizeOptional($def->internalRep);
  }

  $lexemNames = array();
  $lexemLatinNames = array();
  while (merge_compare($def, $currentLexem) < 0) {
    $currentLexem = mysql_fetch_row($lexemDbResult);
  }

  while (merge_compare($def, $currentLexem) == 0) {
    $lexemNames[] = $currentLexem[1];
    $lexemLatinNames[] = text_unicodeToLatin($currentLexem[1]);
    $currentLexem = mysql_fetch_row($lexemDbResult);
  }

  smarty_assign('def', $def);
  smarty_assign('lexemNames', $lexemNames);
  smarty_assign('lexemLatinNames', $lexemLatinNames);
  smarty_assign('source', $sourceMap[$def->sourceId]);
  smarty_assign('user', userCache_get($def->userId));
}

function merge_compare(&$def, &$lexem) {
  if (!$lexem) {
    return 1; // We're at the end of the lexem result set
  } else if ($def->id > $lexem[0]) {
    return -1;
  } else if ($def->id < $lexem[0]) {
    return 1;
  } else {
    return 0;
  }
}

?>