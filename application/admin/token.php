<?php
/*
 * Created on 2013-3-25
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
function getToken($len = 32, $md5 = true)
{
  # Seed random number generator 种子随机数发生器
  # Only needed for PHP versions prior to 4.2 仅适用于4.2之前的PHP版本
  mt_srand((float) microtime() * 1000000);
  # Array of characters, adjust as desiredv 字符数组，根据需要进行调整
  $chars = array(
    'Q',
    '@',
    '8',
    'y',
    '%',
    '^',
    '5',
    'Z',
    '(',
    'G',
    '_',
    'O',
    '`',
    'S',
    '-',
    'N',
    '<',
    'D',
    '{',
    '}',
    '[',
    ']',
    'h',
    ';',
    'W',
    '.',
    '/',
    '|',
    ':',
    '1',
    'E',
    'L',
    '4',
    '&',
    '6',
    '7',
    '#',
    '9',
    'a',
    'A',
    'b',
    'B',
    '~',
    'C',
    'd',
    '>',
    'e',
    '2',
    'f',
    'P',
    'g',
    ')',
    '?',
    'H',
    'i',
    'X',
    'U',
    'J',
    'k',
    'r',
    'l',
    '3',
    't',
    'M',
    'n',
    '=',
    'o',
    '+',
    'p',
    'F',
    'q',
    '!',
    'K',
    'R',
    's',
    'c',
    'm',
    'T',
    'v',
    'j',
    'u',
    'V',
    'w',
    ',',
    'x',
    'I',
    '$',
    'Y',
    'z',
    '*'
  );
  # Array indice friendly number of chars; 数组索引友好的字符数；
  $numChars = count($chars) - 1;
  $token = '';
  # Create random token at the specified length 以指定长度创建随机令牌
  for ($i = 0; $i < $len; $i++)
    $token .= $chars[mt_rand(0, $numChars)];
  # Should token be run through md5? 令牌应该通过md5运行吗
  if ($md5) {
    # Number of 32 char chunks 32个字符块的数量
    $chunks = ceil(strlen($token) / 32);
    $md5token = '';
    # Run each chunk through md5 通过md5运行每个块
    for ($i = 1; $i <= $chunks; $i++)
      $md5token .= md5(substr($token, $i * 32 - 32, 32));
    # Trim the token 修剪令牌
    $token = substr($md5token, 0, $len);
  }
  return $token;
}
