<?php

class WordOfTheDay extends BaseObject {
  public static $_table = 'WordOfTheDay';
  public static $IMAGE_DIR;
  public static $DEFAULT_IMAGE;
  public static $IMAGE_DESCRIPTION_DIR;
  public static $THUMB_DIR;
  public static $THUMB_SIZE;

  public static function init() {
    self::$IMAGE_DIR = util_getRootPath() . "wwwbase/img/wotd";
    self::$DEFAULT_IMAGE = "generic.jpg";
    self::$IMAGE_DESCRIPTION_DIR = util_getRootPath() . "wwwbase/img/wotd/desc";
    self::$THUMB_DIR = util_getRootPath() . "wwwbase/img/wotd/thumb";
    self::$THUMB_SIZE = 48;
  }

  public static function getRSSWotD($delay = 0) {
      $nowDate = ( $delay == 0 ) ? 'NOW()' : 'DATE_SUB(NOW(), INTERVAL ' . $delay. ' MINUTE)';
    return Model::factory('WordOfTheDay')->where_gt('displayDate', '2011-01-01')->where_raw('displayDate < ' . $nowDate)
      ->order_by_desc('displayDate')->limit(25)->find_many();
  }

  public static function getTodaysWord() {
    return Model::factory('WordOfTheDay')->where_raw('displayDate = curdate()')->find_one();
  }

  public static function updateTodaysWord() {
    db_execute('update WordOfTheDay set displayDate=curdate() where displayDate is null order by priority, rand() limit 1');
  }

  public static function getStatus($refId, $refType = 'Definition') {
    $result = Model::factory('WordOfTheDay')->table_alias('W')->select('W.id')->join('WordOfTheDayRel', 'W.id = R.wotdId', 'R')
      ->where('R.refId', $refId)->where('R.refType', $refType)->find_one();
    return $result ? $result->id : NULL;
  }

  public function getImageUrl() {
    if ($this->image && file_exists(self::$IMAGE_DIR . "/{$this->image}")) {
      return "wotd/{$this->image}"; // Relative to the image path
    }

    //fallback to default image
    if (file_exists(self::$IMAGE_DIR . "/" . self::$DEFAULT_IMAGE)) {
      return "wotd/" . self::$DEFAULT_IMAGE;
    }

    return null;
  }

  public function getImageCredits() {
    if (!$this->image) {
      return null;
    }
    $lines = @file(self::$IMAGE_DESCRIPTION_DIR . "/authors.desc");
    if (!$lines) {
      return null;
    }
    foreach ($lines as $line) {
      $commentStart = strpos($line, '#');
      if ($commentStart !== false) {
        $line = substr($line, 0, $commentStart);
      }
      $line = trim($line);
      if ($line) {
        $parts = explode('::', trim($line));
        if (preg_match("/{$parts[0]}/", $this->image)) {
          $filename = self::$IMAGE_DESCRIPTION_DIR . '/' . $parts[1];
          return @file_get_contents($filename); // This could be false if the file does not exist.
        }
      }
    }
    return null;
  }

  public function getThumbUrl() {
    if ($this->image && file_exists(self::$THUMB_DIR . "/{$this->image}")) {
      return "wotd/thumb/{$this->image}"; // Relative to the image path
    }
    return null;
  }

  public function imageFileExists() {
    return $this->image
      ? file_exists(self::$IMAGE_DIR . "/{$this->image}")
      : true; // Not the case since there is no image
  }

  public function ensureThumbnail() {
    if (!$this->image) {
      return;
    }
    $fullImage = self::$IMAGE_DIR . "/{$this->image}";
    $fullThumb = self::$THUMB_DIR . "/{$this->image}";
    if (!file_exists($fullThumb) && file_exists($fullImage)) {
      @mkdir(dirname($fullThumb), 0777, true);
      @chmod(dirname($fullThumb), 0777); // Kill it twice -- mkdir may give it different permissions because of umask
      OS::executeAndAssert(sprintf("convert -strip -geometry %dx%d -sharpen 1x1 '%s' '%s'",
                                   self::$THUMB_SIZE, self::$THUMB_SIZE, $fullImage, $fullThumb));
    }
  }
}

WordOfTheDay::init();

?>
