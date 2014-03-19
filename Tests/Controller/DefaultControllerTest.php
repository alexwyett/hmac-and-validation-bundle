<?php

namespace AW\HmacBundle\Tests\Controller;

use AW\HmacBundle\Tests\TestBase;

class DefaultControllerTest extends TestBase
{    
    /**
     * Test the hmac debug route
     * 
     * @param string  $method HTTP verb
     * @param array   $params Query params
     * @param boolean $result HMAC Result
     * 
     * @dataProvider hmacProvision
     * 
     * @return void
     */
    public function testHmac($method, $params, $result)
    {
        extract(
            $this->doRequest(
                '/hmac/debug',
                $method,
                $params,
                false
            )
        );
        
        $this->assertEquals($result, $json->status);
    }
    
    /**
     * HMAC Provider
     * 
     * @return array
     */
    public function hmacProvision()
    {
        return array(
            array(
                'GET',
                array(
                    'hmacKey' => 'alex',
                    'hmacHash' => '5c89887be0d319c3b328782802c88c7d24353581f0bdbecc5e574f7902b9897c'
                ),
                true
            ),
            array(
                'GET',
                array(
                    'foo' => 'bar',
                    'hmacKey' => 'alex',
                    'hmacHash' => '2eb6f92d777ef9e096cf43b6019d9211cd0542a31ccabad4a3004d63f7134c2a'
                ),
                true
            ),
            array(
                'POST',
                array(
                    'hmacKey' => 'alex',
                    'hmacHash' => 'f6685051d093a38f8ce91e7873ee652a57ee32de3954e058484296f0954222d0',
                    'foo' => 'bar'
                ),
                true
            ),
            array(
                'PUT',
                array(
                    'hmacKey' => 'alex',
                    'hmacHash' => '9b1da3722e19b39c5a892c4691385ef60ae777bc23c27eea9e17376e75cad45f',
                    'foo' => 'bar'
                ),
                true
            ),
            array(
                'DELETE',
                array(
                    'foo' => 'bar',
                    'hmacKey' => 'alex',
                    'hmacHash' => 'e675e05d1341632929248cf370f804eb1e8a7f6f13b2b98313842e4c8dbe1c7e'
                ),
                true
            ),
            array(
                'OPTIONS',
                array(
                    'foo' => 'bar',
                    'hmacKey' => 'alex',
                    'hmacHash' => '67e4b91028ef1ff5a6b4a537e5b64a5235edeea13aac5d086c3e858767a06e88'
                ),
                true
            )
        );
    }
}
