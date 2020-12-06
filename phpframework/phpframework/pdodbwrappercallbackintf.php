<?php

//
// Includes
//

// ...

//
// Types
//

interface PDODBWrapperCallbackIntf
{
    //
    // Public routines
    //

    /**
     * @param string $sql
     * @param mixed[] $arg_array
     */
    public function onPDODBWrapperBeforeExecute($sql, $arg_array);

    /**
     * @param ErrorType $error
     */
    public function onPDODBWrapperError($error);
}

