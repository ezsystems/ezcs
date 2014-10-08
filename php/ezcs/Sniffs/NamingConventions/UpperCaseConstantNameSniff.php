<?php
class ezcs_Sniffs_NamingConventions_UpperCaseConstantNameSniff implements PHP_CodeSniffer_Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_STRING);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens    = $phpcsFile->getTokens();
        $constName = $tokens[$stackPtr]['content'];
        $constNameUpper = strtoupper($constName);

        // If this token is in a heredoc, ignore it.
        if ($phpcsFile->hasCondition($stackPtr, T_START_HEREDOC) === true) {
            return;
        }

        // Special case for PHPUnit.
        if ($constName === 'PHPUnit_MAIN_METHOD') {
            return;
        }

        // If the next non-whitespace token after this token
        // is not an opening parenthesis then it is not a function call.
        $openBracket = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        if ($tokens[$openBracket]['code'] !== T_OPEN_PARENTHESIS) {
            if ($constNameUpper == $constName)
                return;

            $functionKeyword = $phpcsFile->findPrevious(array(T_WHITESPACE, T_COMMA, T_COMMENT, T_STRING), ($stackPtr - 1), null, true);

            switch ($tokens[$functionKeyword]['code']) {
                case T_FUNCTION:
                case T_CLASS:
                case T_INTERFACE:
                case T_IMPLEMENTS:
                case T_EXTENDS:
                case T_INSTANCEOF:
                case T_NEW:
                case T_NAMESPACE:
                case T_USE:
                case T_AS:
                    // This is just a declaration; no constants here.
                    return;
            }
            if ( version_compare( PHP_VERSION, '5.4.0') >= 0 ) {
                switch ($tokens[$functionKeyword]['code']) {
                    case T_TRAIT:
                        return;
                }
            }

            if ($tokens[$functionKeyword]['code'] === T_CONST) {
                // This is a class constant.
                $error = 'Class constants must be uppercase; expected %s but found %s';
                $data  = array(
                          $constNameUpper,
                          $constName,
                         );
                $phpcsFile->addError($error, $stackPtr, 'ClassConstantNotUpperCase', $data);

                return;
            }

            // Is this a class name?
            $nextPtr = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
            switch( $tokens[$nextPtr]['code']) {
                // Is this a class name?
                case T_DOUBLE_COLON:
                // Is this a namespace name?
                case T_NS_SEPARATOR:
                // Is this a goto label?
                case T_COLON:
                    return;
            }

            // Is this a type hint?
            if ($tokens[$nextPtr]['code'] === T_VARIABLE
                || $phpcsFile->isReference($nextPtr) === true
            ) {
                return;
            }

            $prevPtr = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
            switch( $tokens[$prevPtr]['code']) {
                // Is this a member var name?
                case T_OBJECT_OPERATOR:
                // Is this a namespace name?
                case T_NS_SEPARATOR:
                // Is this a goto jump?
                case T_GOTO:
                    return;
            }

            // Is this an instance of declare()
            $prevPtr = $phpcsFile->findPrevious(array(T_WHITESPACE, T_OPEN_PARENTHESIS), ($stackPtr - 1), null, true);
            if ($tokens[$prevPtr]['code'] === T_DECLARE) {
                return;
            }

            // Are we inside an import of namespace?
            if (
                ($prevPtr = $phpcsFile->findPrevious(T_USE, ($stackPtr - 1), null)) !== false &&
                ($nextPtr = $phpcsFile->findNext(T_SEMICOLON, $prevPtr + 1, null)) !== false &&
                $prevPtr < $stackPtr &&
                $nextPtr > $stackPtr
            ) {
                return;
            }

            // This is a real constant.
            $error = 'Constants must be uppercase; expected %s but found %s';
            $data  = array(
                      $constNameUpper,
                      $constName,
                     );
            $phpcsFile->addError($error, $stackPtr, 'ConstantNotUpperCase', $data);

        } else if (strtolower($constName) === 'define' || strtolower($constName) === 'constant') {

            /*
                This may be a "define" or "constant" function call.
            */

            // Make sure this is not a method call.
            $prev = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
            if ($tokens[$prev]['code'] === T_OBJECT_OPERATOR) {
                return;
            }

            // The next non-whitespace token must be the constant name.
            $constPtr = $phpcsFile->findNext(T_WHITESPACE, ($openBracket + 1), null, true);
            if ($tokens[$constPtr]['code'] !== T_CONSTANT_ENCAPSED_STRING) {
                return;
            }

            $constName = $tokens[$constPtr]['content'];
            if (strtoupper($constName) !== $constName) {
                $error = 'Constants must be uppercase; expected %s but found %s';
                $data  = array(
                          strtoupper($constName),
                          $constName,
                         );
                $phpcsFile->addError($error, $stackPtr, 'ConstantNotUpperCase', $data);
            }
        }//end if

    }//end process()


}//end class

?>
