<?php /* #?ini charset="utf-8"?

[ezjscServer]
FunctionList[]=ezstarrating_rate
FunctionList[]=ezstarrating_user_has_rated

[ezjscServer_ezstarrating]
# The server function rate answers POST requests only. ezstarrating.js (as jQuery.ez()) posts to <root>/ezjscore/call/ with
# ezjscServer_function_arguments=ezstarrating::rate::<contentobjectattribute_id>::<version>::<rating>
# and the form token (ezxform_token).
Class=ezsrServerFunctions
Functions[]=ezstarrating
PermissionPrFunction=enabled
File=extension/ezstarrating/classes/ezsrserverfunctions.php

*/ ?>