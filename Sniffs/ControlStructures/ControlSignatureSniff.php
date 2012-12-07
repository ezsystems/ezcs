<?php
/**
 * Verifies that control statements conform to their coding standards.
 */

if (class_exists('PHP_CodeSniffer_Standards_AbstractPatternSniff', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractPatternSniff not found');
}

/**
 * Verifies that control statements conform to their coding standards.
 */
class ezcs_Sniffs_ControlStructures_ControlSignatureSniff extends PHP_CodeSniffer_Standards_AbstractPatternSniff
{

    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array(
                                   'PHP',
                                   'JS',
                                  );


    /**
     * Returns the patterns that this test wishes to verify.
     *
     * @return array(string)
     */
    protected function getPatterns()
    {
        return array(
                'tryEOL...{EOL...}EOL...catch (...)EOL...{EOL',
                'doEOL...{EOL...}EOL...while (...);EOL',
                'while (...)EOL...{EOL',
                'for (...)EOL...{EOL',
                'if (...)EOL...{EOL',
                'foreach (...)EOL...{EOL',
                '}EOLelse if (...)EOL...{EOL',
                '}EOLelseEOL...{EOL',
               );

    }//end getPatterns()


}//end class

?>
