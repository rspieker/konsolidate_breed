<?php


/**
 *  Input verification module, which provides verification of request, cookie and server variables against the
 *  appropriate input buffers
 *  @name    BreedInputVerify
 *  @type    class
 *  @package Breed
 *  @author  Rogier Spieker <rogier@konfirm.net>
 */
class BreedInputVerify
{
	/**
	 *  Verify given key to exist in the buffer with the same (last set) value and strip out NULL bytes from values
	 *  @name    bufferValue
	 *  @type    method
	 *  @access  public
	 *  @param   string buffer
	 *  @param   string key
	 *  @param   mixed  value (one of string or array)
	 *  @param   string separator (divider token between variables)
	 *  @param   bool   rightToLeft (optional, default true, search from the end of the matches)
	 *  @return  mixed  value (one of string, array of boolean false if the value is not verified)
 	 *  @note    This method will enhance security but does not guarantee absolute safety, always check user input!
	 */
	public function bufferValue($buffer, $key, $value, $separator='&', $rightToLeft=true)
	{
		if (!empty($buffer))
			switch (gettype($value))
			{
				case 'string':
					//  see if the given variable can be found in the buffer
					if (preg_match_all('/(' . preg_quote($key) . '=[^' . $separator . ']*)/', $buffer, $match))
					{
						//  prepare the key for easier matching (we need to process the match we put into
						//  parse_str the same way)
						$key = preg_replace('/[\[\]]+/', '_', $key);
						//  traverse the found matches, by default we iterate from right to left (last to first)
						//  as most buffers (GET/POST variables) will override previous set values, but not all
						//  (e.g. COOKIES)
						while ($match[1])
						{
							$verify = $rightToLeft ? array_pop($match[1]) : array_shift($match[1]);
							parse_str(preg_replace('/[\[\]]+/', '_', $verify), $test);

							if (isset($test[$key]) && $test[$key] === $value)
								return strpos($value, "\0") !== false ? str_replace("\0", '', $value) : $value;
						}
					}
					return false;
					break;

				case 'array':
					//  arrays require a little more loving than straight up matching, here we need to match keys and
					//  values, hence an array will stay an array but will get its values set to false if they're
					//  tampered with
					foreach ($value as $k=>$v)
					{
						$test = Array($key . '[' . $k . ']');
						if (is_numeric($k))
							array_unshift($test, $key . '[]');

						foreach ($test as $pattern)
						{
							$value[$k] = $this->bufferValue($buffer, $pattern, $v);
							if ($value[$k])
								break;
						}
					}

					return $value;
					break;
			}
		return $value;
	}
}