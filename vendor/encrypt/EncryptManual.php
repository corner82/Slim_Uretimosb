<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */
namespace Encrypt;

/**
 * Encryption class using Encrypt\AbstractEncrypt
 * @author Mustafa Zeynel Dağlı
 */
class EncryptManual extends \Encrypt\AbstractEncrypt {
    protected  $_hash_lenght;
    protected  $_key;
    protected  $_salt='d41d8cd98f00b204e9800998ecf8427e';
    
    /**
     * constructor
     * @param string $key
     */
    public function __construct($key="")
    {
        if($key!=="")
        {
            // Instead of using the key directly we compress it using a hash function
            $this->_key = $this->hash_value($key);
            // Remember length of hashvalues for later use
            $this->_hash_lenght = strlen($this->_key);
        }
    }

    /**
     * Encrypt any value given counter times
     * @param integer $counter
     * @param string | null $value
     * @param string | null $key
     * @return string
     */
    public function encrypt_times($counter=1,$value="",$key="")
    {
        $output;
        for ($i=0;$i<$counter;$i++)
        {
            $output=$this->encrypt($value,$key);
            $value=$output;
        }
        return $value;
    }

    /**
     * Decrypt any value given counter times
     * @param integer $counter
     * @param string | value $value
     * @param string | null $key
     * @return string
     */
    public function decrypt_times($counter=1,$value="",$key="")
    {
        $output;
        for ($i=0;$i<$counter;$i++)
        {
            $output=$this->decrypt($value,$key);
            $value=$output;
        }
        return $value;  
    }

    /**
     * Encrypt any given value
     * @param string | null $value
     * @param string | null $key
     * @return string
     */
    public function encrypt($value="",$key="")
    {
        $iv = $this->generate_iv();
        // Clear output
        $out = '';
        // First block of output is ($this->_key XOR IV)
        for($c=0;$c < $this->_hash_lenght;$c++) {
                $out .= chr(ord($iv[$c]) ^ ord($this->_key[$c]));
        } 

        // Use IV as first key
        $key = $iv;
        $c = 0;

        // Go through input string
        while($c < strlen($value)) {
            // If we have used all characters of the current key we switch to a new one
            if(($c != 0) and ($c % $this->_hash_lenght == 0)) {
                    // New key is the hash of current key and last block of plaintext
                    $key = $this->hash_value($key . substr($value,$c - $this->_hash_lenght,$this->_hash_lenght));
            }
            // Generate output by xor-ing input and key character for character
            $out .= chr(ord($key[$c % $this->_hash_lenght]) ^ ord($value[$c]));
            $c++;
        }
        // Apply base64 encoding if necessary
        //echo "output not base 64 coded--->". $out. "</br>";
        if($this->_base64) $out =$this->base64_url_encode($out);
        //echo "output  base 64 encoded--->". $out. "</br>";
        return $out;
    }

    /**
     * Decrypt any given value
     * @param string | null $value
     * @param string | null $key
     * @return string
     */
    public function decrypt($value="",$key="")
    {
        // Apply base64 decoding if necessary
        if($this->_base64) $value = $this->base64_url_decode($value);

        // Extract encrypted IV from input
        $tmp_iv = substr($value,0,$this->_hash_lenght);

        // Extract encrypted message from input
        $value = substr($value,$this->_hash_lenght,strlen($value) - $this->_hash_lenght);
        $iv = $out = "";

        // Regenerate IV by xor-ing encrypted IV from block 1 and $this->hashed_key
        // Mathematics: (IV XOR KeY) XOR Key = IV
        for($c=0;$c < $this->_hash_lenght;$c++) {
                $iv .= chr(ord($tmp_iv[$c]) ^ ord($this->_key[$c]));
        }
        // Use IV as key for decrypting the first block cyphertext
        $key = $iv;
        //echo "  key--->".$key."!!!!";
        $c = 0;

        // Loop through the whole input string
        while($c < strlen($value)) {
                // If we have used all characters of the current key we switch to a new one
                if(($c != 0) and ($c % $this->_hash_lenght == 0)) {
                        // New key is the hash of current key and last block of plaintext
                        $key = $this->hash_value(($key . substr($out,$c - $this->_hash_lenght,$this->_hash_lenght)));
                }
                // Generate output by xor-ing input and key character for character
                $out .= chr(ord($key[$c % $this->_hash_lenght]) ^ ord($value[$c]));
                $c++;
        }
        return $out;
    }


    /**
    * Hashfunction used for encryption
    *
    * This class hashes any given string using the best available hash algorithm.
    * Currently support for md5 and sha1 is provided. In theory even crc32 could be used
    * but I don't recommend this.
    *
    * @access	private
    * @param	string	$string	Message to hashed
    * @return string	Hash value of input message
    */
    public function hash_value($string) {
        // Use sha1() if possible, php versions >= 4.3.0 and 5
        if(function_exists('sha1')) {
            $hash = sha1($string);
        } else {
            // Fall back to md5(), php versions 3, 4, 5
            $hash = md5($string);
        }
        $out ='';
        // Convert hexadecimal hash value to binary string
        for($c=0;$c<strlen($hash);$c+=2) {
            $out .= $this->hex_to_chr($hash[$c] . $hash[$c+1]);
        }
        return $out;
    }

    /**
    * Generate a random string to initialize encryption
    *
    * This method will return a random binary string IV ( = initialization vector).
    * The randomness of this string is one of the crucial points of this algorithm as it
    * is the basis of encryption. The encrypted IV will be added to the encrypted message
    * to make decryption possible. The transmitted IV will be encoded using the user provided key.
    *
    * @todo	Add more random sources.
    * @access	public
    * @see function	hash_encryption
    * @return string	Binary pseudo random string
    **/
    public function generate_iv() {
        // Initialize pseudo random generator
        srand ((double)microtime()*1000000);

        // Collect random data.
        // Add as many "pseudo" random sources as you can find.
        // Possible sources: Memory usage, diskusage, file and directory content...
        $iv  = $this->_salt;
        $iv .= rand(0,getrandmax());
        // Changed to serialize as the second parameter to print_r is not available in php prior to version 4.4
        $iv .= serialize($GLOBALS);
        return $this->hash_value($iv);
    }

    /**
    * Convert hexadecimal value to a binary string
    *
    * This method converts any given hexadecimal number between 00 and ff to the corresponding ASCII char
    *
    * @access	private
    * @param	string	Hexadecimal number between 00 and ff
    * @return	string	Character representation of input value
    **/
    private function hex_to_chr($num) {
            return chr(hexdec($num));
    }
}
