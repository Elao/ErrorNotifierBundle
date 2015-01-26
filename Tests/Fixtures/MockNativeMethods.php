<?php

namespace {
    $errorReportingResult   = null;
    $timeResult             = null;
    $isDirResult            = null;
    $isFileResult           = null;
    $fileGetContentsResult  = null;
    $overrideFilePutContents    = null;
    $errorGetLastResult     = null;
    $setErrorHandlerMethod  = null;
    $setRegisterShutdownFunctionMethod  = null;
}

namespace Elao\ErrorNotifierBundle\Tests\DecisionManagement\Decider {
    function set_native_error_reporting($result = null)
    {
        global $errorReportingResult;

        $errorReportingResult = $result;
    }

    function set_native_time($result = null)
    {
        global $timeResult;

        $timeResult = $result;
    }

    function set_native_is_dir_result($result = null)
    {
        global $isDirResult;

        $isDirResult = $result;
    }

    function set_native_is_file_result($result = null)
    {
        global $isFileResult;

        $isFileResult = $result;
    }

    function set_native_file_get_contents_result($result = null)
    {
        global $fileGetContentsResult;

        $fileGetContentsResult = $result;
    }

    function set_override_native_file_put_contents($override)
    {
        global $overrideFilePutContents;

        $overrideFilePutContents = true === $override;
    }
}

namespace Elao\ErrorNotifierBundle\DecisionManagement\Decider {
    function error_reporting()
    {
        global $errorReportingResult;

        if (null === $errorReportingResult) {
            return call_user_func_array('\error_reporting', func_get_args());
        }

        return $errorReportingResult;
    }

    function time()
    {
        global $timeResult;

        if (null === $timeResult) {
            return call_user_func_array('\time', func_get_args());
        }

        return $timeResult;
    }

    function is_dir()
    {
        global $isDirResult;

        if (null === $isDirResult) {
            return call_user_func_array('\is_dir', func_get_args());
        }

        return $isDirResult;
    }

    function is_file()
    {
        global $isFileResult;

        if (null === $isFileResult) {
            return call_user_func_array('\is_file', func_get_args());
        }

        return $isFileResult;
    }

    function file_get_contents()
    {
        global $fileGetContentsResult;

        if (null === $fileGetContentsResult) {
            return call_user_func_array('\file_get_contents', func_get_args());
        }

        return $fileGetContentsResult;
    }

    function file_put_contents()
    {
        global $overrideFilePutContents;

        if (false === $overrideFilePutContents) {
            return call_user_func_array('\file_put_contents', func_get_args());
        }

        return true;
    }
}


namespace Elao\ErrorNotifierBundle\Tests\Handler {
    function set_native_error_get_last($result = null)
    {
        global $errorGetLastResult;

        $errorGetLastResult = $result;
    }
}

namespace Elao\ErrorNotifierBundle\Handler {
    function error_get_last()
    {
        global $errorGetLastResult;

        if (null === $errorGetLastResult) {
            return call_user_func_array('\error_get_last', func_get_args());
        }

        return $errorGetLastResult;
    }
}

namespace Elao\ErrorNotifierBundle\Tests\Listener {
    function set_set_error_handler_method($method = null)
    {
        global $setErrorHandlerMethod;

        $setErrorHandlerMethod = $method;
    }

    function set_register_shutdown_function_method($method = null)
    {
        global $setRegisterShutdownFunctionMethod;

        $setRegisterShutdownFunctionMethod = $method;
    }
}

namespace Elao\ErrorNotifierBundle\Listener {
    function set_error_handler()
    {
        global $setErrorHandlerMethod;

        if (null === $setErrorHandlerMethod) {
            return call_user_func_array('\set_error_handler', func_get_args());
        }

        return call_user_func_array($setErrorHandlerMethod, func_get_args());
    }

    function register_shutdown_function()
    {
        global $setRegisterShutdownFunctionMethod;

        if (null === $setRegisterShutdownFunctionMethod) {
            return call_user_func_array('\register_shutdown_function', func_get_args());
        }

        return call_user_func_array($setRegisterShutdownFunctionMethod, func_get_args());
    }
}
