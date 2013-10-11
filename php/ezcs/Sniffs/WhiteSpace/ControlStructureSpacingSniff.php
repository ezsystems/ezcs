<?php
class ezcs_Sniffs_WhiteSpace_ControlStructureSpacingSniff implements PHP_CodeSniffer_Sniff
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
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_IF,
                T_WHILE,
                T_FOREACH,
                T_FOR,
                T_SWITCH,
                T_DO,
                T_ELSE,
                T_ELSEIF,
               );

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['scope_closer']) === false) {
            return;
        }

        if (isset($tokens[$stackPtr]['parenthesis_opener']) === true) {
            $parenOpener = $tokens[$stackPtr]['parenthesis_opener'];
            $parenCloser = $tokens[$stackPtr]['parenthesis_closer'];
            if ($tokens[($parenOpener + 1)]['code'] !== T_WHITESPACE) {
                $phpcsFile->addError("Expected 1 space after opening bracket; none found", ($parenOpener + 1), 'SpacingAfterOpenBrace');
            } elseif (($gap = strlen($tokens[($parenOpener + 1)]['content'])) !== 1) {
                $phpcsFile->addError("Expected 1 space after opening bracket; %s found", ($parenOpener + 1), 'SpacingAfterOpenBrace', array($gap));
            }

            if ($tokens[$parenOpener]['line'] === $tokens[$parenCloser]['line']) {
                if ( $tokens[($parenCloser - 1)]['code'] !== T_WHITESPACE) {
                    $phpcsFile->addError("Expected 1 space before closing bracket; none found", ($parenOpener + 1), 'SpaceBeforeCloseBrace');
                } elseif (($gap = strlen($tokens[($parenCloser - 1)]['content'])) !== 1) {
                    $phpcsFile->addError("Expected 1 space before closing bracket; %s found", ($parenCloser - 1), 'SpaceBeforeCloseBrace', array($gap));
                }
            }
        }//end if

        $scopeOpener = $tokens[$stackPtr]['scope_opener'];
        $scopeCloser = $tokens[$stackPtr]['scope_closer'];

        $firstContent = $phpcsFile->findNext(
            T_WHITESPACE,
            ($scopeOpener + 1),
            null,
            true
        );

        if ($tokens[$firstContent]['line'] !== ($tokens[$scopeOpener]['line'] + 1)) {
            $phpcsFile->addError("Blank line found at start of control structure", $scopeOpener, 'SpacingBeforeOpen');
        }

        $lastContent = $phpcsFile->findPrevious(
            T_WHITESPACE,
            ($scopeCloser - 1),
            null,
            true
        );

        if ($tokens[$lastContent]['line'] !== ($tokens[$scopeCloser]['line'] - 1)) {
            $errorToken = $scopeCloser;
            for ($i = ($scopeCloser - 1); $i > $lastContent; $i--) {
                if ($tokens[$i]['line'] < $tokens[$scopeCloser]['line']) {
                    $errorToken = $i;
                    break;
                }
            }

            $phpcsFile->addError("Blank line found at end of control structure", $errorToken, 'SpacingAfterClose');
        }
    }//end process()
}//end class

?>
