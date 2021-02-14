<?
// File:       tea.php
// Contents:   encrypts 32-bit integer into 16-char hex string and decrypts it back
// Created:    10.09.2011
// Programmer: Edward A. Shiryaev (adaptation)

class tea {

  // $v    - arrays[8] of bytes
  // $key  - array[4] of int32
  // returns encoded arrays[8] of bytes
  private static function encipher($v,$key) {
  
        $delta = 0x9E3779B9;
    $n = 32;
  
    $y = (($v[0] & 0xff) << 24) | (($v[1] & 0xff) << 16) | (($v[2] & 0xff) << 8) | ($v[3] & 0xff);
    $z = (($v[4] & 0xff) << 24) | (($v[5] & 0xff) << 16) | (($v[6] & 0xff) << 8) | ($v[7] & 0xff); 
    
    $sum = 0;
    while(($n--) > 0) {
        $y = self::add($y, self::add( self::add((($z << 4) ^ self::rshift($z,5)) , ($z ^ $sum)) , $key[($sum & 3)]) );
        $sum = self::add($sum,$delta);
        $z = self::add($z, self::add( self::add((($y << 4) ^ self::rshift($y,5)) , ($y ^ $sum)) , $key[self::rshift($sum,11) & 3]));	    
    }
  
    $w = array(
      self::rshift($y , 24),
      (self::rshift($y , 16) & 0xFF),
      (self::rshift($y , 8) & 0xFF),
      ($y & 0xFF),
      self::rshift($z , 24),
      (self::rshift($z , 16) & 0xFF),
      (self::rshift($z , 8) & 0xFF),
      ($z & 0xFF)
    );
    return $w;
      }
  
  // $w    - arrays[8] of bytes
  // $key  - array[4] of int32
  // returns $v (arrays[8] of bytes)
  private static function decipher($w,$key) {
        $delta = 0x9E3779B9;
    $n = 32;
  
    $y = (($w[0] & 0xff) << 24) | (($w[1] & 0xff) << 16) | (($w[2] & 0xff) << 8) | ($w[3] & 0xff);
    $z = (($w[4] & 0xff) << 24) | (($w[5] & 0xff) << 16) | (($w[6] & 0xff) << 8) | ($w[7] & 0xff); 
  
          $sum = 0xC6EF3720; // $delta * $n;
          while (($n--) > 0)  {
              $z = self::add($z, -self::add(self::add(($y << 4) ^ self::rshift($y , 5),($y ^ $sum)) , $key[self::rshift($sum,11) & 3]));
              $sum = self::add($sum,-$delta);
              $y = self::add($y,-self::add(self::add(($z << 4) ^ self::rshift($z , 5), ($z ^ $sum)) , $key[$sum &3]));
          }
  
    $v = array(
      self::rshift($y , 24),
      self::rshift($y , 16) & 0xFF,
      self::rshift($y , 8) & 0xFF,
      ($y & 0xFF),
      self::rshift($z , 24),
      self::rshift($z , 16) & 0xFF,
      self::rshift($z , 8) & 0xFF,
      ($z & 0xFF)
    );
    
    return $v;
  }
  
  // a utility function used by encipher() and decipher()
  private static function rshift($integer, $n) {
          // convert to 32 bits
          if (0xffffffff < $integer || -0xffffffff > $integer) {
              $integer = fmod($integer, 0xffffffff + 1);
          }
  
          // convert to unsigned integer
          if (0x7fffffff < $integer) {
              $integer -= 0xffffffff + 1.0;
          } elseif (-0x80000000 > $integer) {
              $integer += 0xffffffff + 1.0;
          }
  
          // do right shift
          if (0 > $integer) {
              $integer &= 0x7fffffff;                     // remove sign bit before shift
              $integer >>= $n;                            // right shift
              $integer |= 1 << (31 - $n);                 // set shifted sign bit
          } else {
              $integer >>= $n;                            // use normal right shift
          }
  
          return $integer;
  }
  
  // a utility function used by encipher() and decipher()
  private static function add($i1, $i2) {
          $result = 0.0;
          foreach (func_get_args() as $value) {
              // remove sign if necessary
              if (0.0 > $value) {
                  $value -= 1.0 + 0xffffffff;
              }
              $result += $value;
          }
  
          // convert to 32 bits
          if (0xffffffff < $result || -0xffffffff > $result) {
              $result = fmod($result, 0xffffffff + 1);
          }
  
          // convert to signed integer
          if (0x7fffffff < $result) {
              $result -= 0xffffffff + 1.0;
          } elseif (-0x80000000 > $result) {
              $result += 0xffffffff + 1.0;
          }
  
          return $result;
  }
  
  //---- tunable area ----
  
  // $key - array[4] of int32
  private static $KEY = array( 0x63, 0x61, 0x62, 0x6c );  // 'cabl'
  
  //---- public area ----
  
  // $n - 32-bit number to encrypt
  // return encrypted number as hex string
  public static function encrypt($n) {
      $v = array( 0,0,0,0,
           self::rshift($n,24) & 0xFF,
           self::rshift($n,16) & 0xFF,
           self::rshift($n, 8) & 0xFF,
           $n & 0xFF
      );
      $w = self::encipher($v,self::$KEY);
      return sprintf('%02x%02x%02x%02x%02x%02x%02x%02x',
           $w[7],$w[6],$w[5],$w[4],$w[3],$w[2],$w[1],$w[0]);
  }
  
  // $s - encrypted number as hex string
  // return decrypted 32-bit number, or NULL if $s is not a 16-digit hex string
  // edw: added NULL return value for invalid input number
  public static function decrypt($s) {
      $w = array();
      if(sscanf($s,'%2x%2x%2x%2x%2x%2x%2x%2x', $w[7],$w[6],$w[5],$w[4],$w[3],$w[2],$w[1],$w[0]) != 8)
        return NULL;
      $v = self::decipher($w,self::$KEY);
      return ($v[4] << 24) | ($v[5] << 16) | ($v[6] << 8) | $v[7];
  }

}
?>