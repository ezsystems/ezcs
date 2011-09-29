<?php
/**
 * This sniff class detected empty statement.
 */
class ezpnext_Sniffs_CodeAnalysis_EmptyStatementSniff extends Generic_Sniffs_CodeAnalysis_EmptyStatementSniff
{

    /**
     * List of block tokens that this sniff covers.
     *
     * The key of this hash identifies the required token while the boolean
     * value says mark an error or mark a warning.
     *
     * @var array
     */
    protected $checkedTokens = array(
                                T_DO      => false,
                                T_ELSE    => false,
                                T_ELSEIF  => false,
                                T_FOR     => false,
                                T_FOREACH => false,
                                T_IF      => false,
                                T_SWITCH  => false,
                                T_TRY     => false,
                                T_WHILE   => false,
                               );
}

?>
