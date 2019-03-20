<?php
namespace exussum12\CoverageChecker\Outputs;

use exussum12\CoverageChecker\Output;

class Junit implements Output
{

    public function output($coverage, $percent, $minimumPercent)
    {
        $violations = [];
        foreach ($coverage as $file => $lines) {
            foreach ($lines as $line => $error) {
                $violations[$file][] = (object) [
                    'lineNumber' => $line,
                    'message' => $error
                ];
            }
        }
        $output = (object) [
            'coverage' => number_format($percent, 2),
            'status' => $percent >= $minimumPercent ?
                'Passed':
                'Failed',
            'violations' => $violations
        ];
       // var_dump($coverage); die();

        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $rootElement = $xml->createElement('testsuites');
        $xml->appendChild($rootElement);

        foreach ($coverage as $file => $lines) {
            $testSuite = new \DOMElement('testsuite');
            $rootElement->appendChild($testSuite);

            $testSuite->setAttribute('name', $file);

            $lineCount = 0;

            foreach ($lines as $line => $error) {
                $lineCount++;

                foreach ($error as $err) {
                    $testCase = new \DOMElement('testcase');
                    $testSuite->appendChild($testCase);

                    $testCase->setAttribute('name', sprintf("%s (%s)", $file, $line));

                    $testCaseError = new \DOMElement('failure');
                    $testCase->appendChild($testCaseError);

                    $testCaseError->setAttribute('type', 'error');
                    $testCaseError->setAttribute('message', $err);
                }

            }
        }

        print($xml->saveXML());
        die();
    }
}
