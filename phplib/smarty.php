<?php

require_once(pref_getSmartyClass());

// Create an instance of Smarty, assign some default parameters for the
// header and footer and return it.
function smarty_init() {
  $smarty = new Smarty;
  $smarty->template_dir = util_getRootPath() . 'templates';
  $smarty->compile_dir = util_getRootPath() . 'templates_c';
  $smarty->assign('wwwRoot', util_getWwwRoot());
  $smarty->assign('cssRoot', util_getCssRoot());
  $smarty->assign('imgRoot', util_getImgRoot());
  $smarty->assign('sources', Source::loadAllSources());
  $smarty->assign('is_connected', session_userExists());
  $smarty->assign('is_moderator', session_userIsModerator());
  $smarty->assign('is_flex_moderator', session_userIsFlexModerator());
  $smarty->assign('is_mirror', pref_isMirror());
  $smarty->assign('nick', session_getUserNick());
  $smarty->assign('main_page', $_SERVER['PHP_SELF'] == "/index.php");
  $wordCount = Definition::getWordCount();
  $wordCountRough = $wordCount - ($wordCount % 10000);
  $smarty->assign('words_total', util_formatNumber($wordCount, 0));
  $smarty->assign('words_rough', util_formatNumber($wordCountRough, 0));
  $smarty->assign('words_last_month',
		  util_formatNumber(Definition::getWordCountLastMonth(), 0));
  $smarty->assign('contact_email', pref_getContactEmail());
  $smarty->assign('debug', session_isDebug());
  $smarty->assign('show_search_box', 1);
  $smarty->assign('focus_search_box', 1);
  $smarty->assign('hostedBy', pref_getHostedBy());
  $GLOBALS['smarty_theSmarty'] = $smarty;
}

function smarty_isInitialized() {
  return array_key_exists('smarty_theSmarty', $GLOBALS);
}

function smarty_displayCommonPageWithSkin($templateName) {
  smarty_assign('contentTemplateName', "common/$templateName");
  $fileName = session_getSkin() . '/pageLayout.ihtml';
  $GLOBALS['smarty_theSmarty']->display($fileName);
}

function smarty_displayPageWithSkin($templateName) {
  $skin = session_getSkin();
  smarty_assign('contentTemplateName', "$skin/$templateName");
  $fileName = "$skin/pageLayout.ihtml";
  $GLOBALS['smarty_theSmarty']->display($fileName);
}

function smarty_displayWithoutSkin($templateName) {
  $GLOBALS['smarty_theSmarty']->display($templateName);
}

function smarty_assign($variable, $value) {
  $GLOBALS['smarty_theSmarty']->assign($variable, $value);
}
?>