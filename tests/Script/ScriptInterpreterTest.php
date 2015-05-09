<?php

namespace BitWasp\Bitcoin\Tests\Script;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Script\Script;
use BitWasp\Bitcoin\Script\ScriptFactory;
use BitWasp\Bitcoin\Script\ScriptInterface;
use BitWasp\Bitcoin\Script\ScriptInterpreter;
use BitWasp\Bitcoin\Transaction\Transaction;
use BitWasp\Bitcoin\Script\ScriptInterpreterFlags;
use BitWasp\Buffertools\Buffer;

class ScriptInterpreterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $flagStr
     * @return ScriptInterpreterFlags
     */
    private function setFlags($flagStr)
    {
        $array = explode(",", $flagStr);
        $flags = new ScriptInterpreterFlags();
        foreach ($array as $activeFlag) {
            $flags->$activeFlag = true;
        }
        return $flags;
    }

    public function testS()
    {
        $ec = Bitcoin::getEcAdapter();

        $hex = '01007676768888';
        $buffer = Buffer::hex($hex);
        $script = new Script($buffer);

        $f = new ScriptInterpreterFlags();
        $i = new ScriptInterpreter($ec, new Transaction(), $f);
        $i->setScript($script);

        $this->assertTrue($i->run());
    }

    public function getScripts()
    {
        $f = file_get_contents(__DIR__ . '/../Data/scriptinterpreter.simple.json');
        $json = json_decode($f);

        $vectors = [];
        foreach ($json->test as $c => $test) {
            $flags = $this->setFlags($test->flags);
            $scriptSig = ScriptFactory::fromHex($test->scriptSig);
            $scriptPubKey = ScriptFactory::fromHex($test->scriptPubKey);
            $vectors[] = [
                $flags, $scriptSig, $scriptPubKey, $test->result, $test->desc
            ];
        }
        return $vectors;
    }

    /**
     * @dataProvider getScripts
     * @param ScriptInterpreterFlags $flags
     * @param ScriptInterface $scriptSig
     * @param ScriptInterface $scriptPubKey
     */
    public function testScript(ScriptInterpreterFlags $flags, ScriptInterface $scriptSig, ScriptInterface $scriptPubKey, $result, $description)
    {
        $i = new ScriptInterpreter(Bitcoin::getEcAdapter(), new Transaction(), $flags);
        $i->setScript($scriptSig)->run();
        $testResult = $i->setScript($scriptPubKey)->run();
        var_dump($scriptPubKey->getScriptParser()->getHumanReadable());
        $this->assertEquals($result, $testResult, $description);
    }

}
