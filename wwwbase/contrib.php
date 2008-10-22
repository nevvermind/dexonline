<?php
require_once("../phplib/util.php");
util_assertNotMirror();

$name = util_getRequestParameter('wordName');
$sourceId = util_getRequestParameter('source');
$def = util_getRequestParameter('def');
$sendButton = util_getRequestParameter('send');

if ($sendButton) {
  session_setSourceCookie($sourceId);

  $def = text_internalizeDefinition($def);

  $errorMessage = '';
  if (!$name) {
    $errorMessage = 'Trebuie să introduceţi un cuvânt-titlu.';
  } else if (!$def) {
    $errorMessage = 'Trebuie să introduceţi o definiţie.';
  }

  if ($errorMessage) {
    smarty_assign('wordName', $name);
    smarty_assign('sourceId', $sourceId);
    smarty_assign('def', $def);
    smarty_assign('errorMessage', $errorMessage);
    smarty_assign('previewDivContent', text_htmlize($def));
  } else {
    $definition = new Definition();
    $definition->userId = session_getUserId();
    $definition->sourceId = $sourceId;
    $definition->internalRep = $def;
    $definition->htmlRep = text_htmlize($def);
    $definition->lexicon = text_extractLexicon($definition);
    $definition->save();
    $definition->id = db_getLastInsertedId();

    $lexems = Lexem::loadByUnaccented($name);
    if (count($lexems)) {
      // Reuse existing lexem.
      $lexem = $lexems[0];
    } else {
      // Create a new lexem.
      $lexem = Lexem::create($name, 'T', '1', '');
      $lexem->save();
      $lexem->id = db_getLastInsertedId();
      $lexem->regenerateParadigm();
    }

    LexemDefinitionMap::associate($lexem->id, $definition->id);

    smarty_assign('submissionSuccessful', 1);
    smarty_assign('sourceId', session_getDefaultContribSourceId());
  }
} else {
  smarty_assign('sourceId', session_getDefaultContribSourceId());
}

smarty_assign('contribSources', Source::loadAllContribSources());
smarty_displayWithoutSkin('common/contrib.ihtml');

?>