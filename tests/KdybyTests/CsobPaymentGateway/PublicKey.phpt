<?php

/**
 * PublicKey
 *
 * @testCase
 */

namespace KdybyTests\CsobPaymentGateway;

use Kdyby;
use Kdyby\CsobPaymentGateway\Certificate\PublicKey;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class PublicKeyTest extends Tester\TestCase
{

	/**
	 * @var PublicKey
	 */
	private $key;



	protected function setUp()
	{
		$this->key = new PublicKey(__DIR__ . '/../../../examples/keys/rsa_A1029DTmM7.pub');
	}



	public function testVerify()
	{
		Assert::true($this->key->verify(
			'Kdyby',
			'i1RUnSZtA+cSnQ0eW2qGTFlyU8jdWxfGLD1OYXy8opqNrRW/3jZwpFWJmdh0kkt74wreS/mvqD56vYRs8l922A2OFI4A+VbplBQg+BiDosQKNuc3WiVqJK72BpSXVMX6xTB1Tcm/2yI9jwgQv4D9m5ORmeHF+17bqQLjUDOTqERIXIQPS9PlNxbZfXgnkFUqW5VMmPL6xPtB17l0M7QI38VQaZXw5SJTELCS3c8OlxQFe/9xyy2zao1rHUVScbinq3Msgv3X3LAyRDc98XpixhMSX39oRGGq/HyeSEljKO1kbr+ujGGTaHxUkTaRH+fh0uNtMsPfdYJuT8A6YqUjwg=='
		));
	}

}

\run(new PublicKeyTest());
