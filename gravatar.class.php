<?php
/**
 * Class to handle gravatar.com requests
 *
 * @author     Knut Kohl <knutkohl@users.sourceforge.net>
 * @copyright  2011 Knut Kohl
 * @licence    GNU General Public License - http://www.gnu.org/licenses/gpl.txt
 * @version    0.1.0
 */
class Gravatar {

  const URL_HTTP  = 'http://www.gravatar.com/';

  const URL_HTTPS = 'https://secure.gravatar.com/';

  // -------------------------------------------------------------------------
  // PUBLIC
  // -------------------------------------------------------------------------

  /**
   * Class constructor
   *
   * Set defaults
   *
   * @return void
   */
  public function __construct() {
    $this->reset();
  } // function __construct()

  /**
   * Set avatar size in pixels, defaults to 80px
   *
   * @param string $size Size in pixels [1-512]
   * @return instance $this
   */
  public function setSize( $size ) {
    if ($s >= 1 AND $s <= 512) {
      $this->_size = (int) $size;
    }
    return $this;
  } // function setSize()

  /**
   * Get avatar size in pixels
   *
   * @return int
   */
  public function getSize() {
    return $this->_size;
  }

  /**
   * Set avatar size in pixels, defaults to 80px
   *
   * @param string $size Size in pixels [1-512]
   * @return instance $this
   */
  public function setUseExtension( $use=TRUE ) {
    $this->_useextension = (bool) $use;
    return $this->_size;
  } // function setUseExtension()

  /**
   * Get avatar size in pixels
   *
   * @return int
   */
  public function getUseExtension() {
    return $this->_useextension;
  } // function getUseExtension()

  /**
   * Set default imageset to use, defaults to 'mm'
   *
   * Defaults:
   * 404: do not load any image if none is associated with the email hash, instead return an HTTP 404 (File Not Found) response
   * mm: (mystery-man) a simple, cartoon-style silhouetted outline of a person (does not vary by email hash)
   * identicon: a geometric pattern based on an email hash
   * monsterid: a generated 'monster' with different colors, faces, etc
   * wavatar: generated faces with differing features and backgrounds
	 * retro: awesome generated, 8-bit arcade-style pixelated faces
   *
   * If an other value is provided, assume it is a URL and urlencode() it
   *
   * Force Default
   * If for some reason you wanted to force the default image to always load,
   * you can do that by setting this value to 'y'
   *
	 * @param string $default Default imageset to use [404|mm|identicon|monsterid|wavatar|y]
   * @return instance $this
   */
  public function setImageset( $default ) {
    if (in_array($default, array('404','mm','identicon','monsterid','wavatar'))) {
      $this->_default = $default;
    } else {
      $this->_default = urlencode($default);
    }
    return $this;
  }

  /**
   * Get default imageset to use
   *
   * @return string
   */
  public function getImageset() {
    return $this->_default;
  }

  /**
   * Set maximum rating (inclusive), defaults to 'g'
   *
   * You may specify one of the following ratings to request images up to and
   * including that rating:
   * g:  suitable for display on all websites with any audience type
   * pg: may contain rude gestures, provocatively dressed individuals,
   *     the lesser swear words, or mild violence
   * r:  may contain such things as harsh profanity, intense violence, nudity,
   *     or hard drug use
   * x:  may contain hardcore sexual imagery or extremely disturbing violence
   *
	 * @param string $rating Maximum rating (inclusive) [g|pg|r|x]
   * @return instance $this
   */
  public function setMaxRating( $rating ) {
    if (in_array($r, array('g','pg','r','x'))) {
      $this->_rating = $rating;
    }
    return $this;
  }

  /**
   * Get maximum rating (inclusive)
   *
   * @return string
   */
  public function getMaxRating() {
    return $this->_rating;
  }

  /**
   * Use secure connection
   *
	 * @param bool $secure
   * @return instance $this
   */
  public function setSecure( $secure ) {
    $this->_secure = (bool) $secure;
    return $this;
  }

  /**
   * Get usage of secure connection
   *
   * @return instance $this
   */
  public function getSecure() {
    return $this->_secure;
  }

  /**
   * Set extra parameters for request URL
   *
	 * @param string $param
	 * @param string $value
   * @return instance $this
   */
  public function setParam( $param, $value=NULL ) {
    if (isset($value))
      $this->_params[$param] = $value;
    else
      unset($this->_params[$param]);
    return $this;
  }

  /**
   * Build URL to retrieve infos from gravatar.com
   *
   * Data Formats
   * - JSON
   * - XML
   * - PHP
   * - VCF/vCard
   * - QR Code (contain a link to the main profile page)
   *
   * @param string $email  Email adress
   * @param string $format Requested result format [json|xml|php|vcf|qr],
   *                       defaults to 'php'
   * @param bool   $params Add parameters to URL (for QR code)
   * @return string
   */
  public function getInfoURL( $email, $format='php', $params=FALSE ) {
    $url = $this->_secure ? self::URL_HTTPS : self::URL_HTTP;
    $url .= md5(trim(strtolower($email))).'.'.$format;
    $url .= $this->_buildParams($params);
    return $url;
  } // function getInfoURL()

  /**
   * Get account info
   *
   * Data Formats
   * - JSON
   * - XML
   * - PHP
   * - VCF/vCard
   *
   * Possible info data (json,xml,php):
   * - id
   * - hash
   * - requestHash
   * - profileUrl
   * - preferredUsername
   * - thumbnailUrl
   * - urls (array)
   * - photos (array)
   *   - value
   *   - type
   *   - name
   *   - displayName
   *
   * @param string $email Email adress
   * @param string $format Requested result format [json|xml|php|vcf],
   *                       defaults to 'php'
   * @return array|string Returns "No valid user" in case of an error
   */
  public function getInfo( $email, $format='php' ) {
    $temp = @file_get_contents($this->getInfoURL($email, $format));
    $utemp = @unserialize($temp); // works ONLY for php format!
    if (isset($utemp['entry'][0]) AND is_array($utemp['entry'][0])) {
      $utemp = $utemp['entry'][0];
      return array_change_key_case($utemp, CASE_LOWER);
    } else {
      return $temp; // "User not found" or non-(php)serialized data
    }
  } // function getInfo()

  /**
   * @param string $email Email adress
   * @param bool   $img   TRUE to return a complete IMG tag; FALSE for just the URL
   * @param array  $atts  Optional, additional key/value attributes to include
   *                      in the IMG tag
   * @return string
   */
  public function getGravatarURL( $email, $img=FALSE, $atts=array() ) {
    $url = $this->_secure ? self::URL_HTTPS : self::URL_HTTP;
    $url .= 'avatar/' . md5(trim(strtolower($email)));
    if ($this->_usextension) {
      $url .= '.jpg';
    }
    $url .= $this->_buildParams(TRUE);
    if ($img) {
      $url = '<img width="'.$this->_size.'" height="'.$this->_size.'" src="'.$url.'"';
      foreach ($atts as $key=>$val) {
        $url .= ' '.$key.'="'.$val.'"';
      }
      $url .= '/>';
    }
    return $url;
  } // function getGravatarURL()

  /**
   * Set to defaults
   *
   * @return instance $this
   */
  public function reset() {
    $this->_secure      = FALSE;
    $this->_size        = 80;
    $this->_usextension = TRUE;
    $this->_default     = 'mm';
    $this->_rating      = 'g';
    $this->_params      = array();
  } // function reset()

  // -------------------------------------------------------------------------
  // PROTECTED
  // -------------------------------------------------------------------------

  /**
   * @var  string
   */
  protected $_secure;

  /**
   * Size in pixels, defaults to 80px [1-512]
   *
   * @var  array
   */
  protected $_size;

  /**
   * Use .jpg on gravatar requests
   *
   * @var  bool
   */
  protected $_usextension;

  /**
   * Default imageset to use [404|mm|identicon|monsterid|wavatar]
   *
   * @var  array
   */
  protected $_default;

  /**
   * Maximum rating (inclusive) [g|pg|r|x]
   *
   * @var  array
   */
  protected $_rating;

  /**
   * @var  array
   */
  protected $_params;

  /**
   * @param bool $img TRUE to return a complete IMG tag; FALSE for just the URL
   * @return string
   */
  protected function _buildParams( $gravatar ) {
    $return = '?';
    if ($gravatar) {
      $return .= '?s=' . $this->_size . '&amp;r=' . $this->_rating . '&amp;'
                 // if default is set to y, use parameter 'f' (force)
               . (($this->_default != 'y') ? 'd' : 'f') . '=' . $this->_default;
    }
    foreach ($this->_params as $key=>$value) {
      $return .= '&amp;'.$key.'='.$value;
    }
    return trim($return, '?');
  }

}