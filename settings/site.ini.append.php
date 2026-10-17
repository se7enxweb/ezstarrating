<?php /* #?ini charset="utf-8"?

[TemplateSettings]
ExtensionAutoloadPath[]=ezstarrating

[RegionalSettings]
TranslationExtensions[]=ezstarrating
   

[eZStarRating]
# Avoid that users are allowed to rate content several times by
# logging in as different users. Done by storing content object
# attribute in a session variable (witch is kept even if you login/out)
UseUserSession=enabled

# Allows the user to change his rating after he has rated by returning to the page and rate again
AllowChangeRating=enabled

# Allow anonymous user to rate content.
#  You also need :
#    - to give access to anonymous users to starrating ezjscore functions
#    - to enable the UseUserSession setting to avoid anonymous to be considered as a single individual
#
#  Activating this option (along with UseUserSession):
#    - Will create a session for every anonymous user rating content.
#    - might allow spamming since anonymous user is only authenticated by its session cookie.
#
#  Relation with the Session/ForceStart setting:
#    If Session/ForceStart is set to enabled: Existing sessions will be used
#    if Session/ForceStart is set to disabled: New sessions will be created for any user who starts rating content
AllowAnonymousRating=disabled

# The server function ezstarrating::rate changes data, so it answers POST requests only: the form token check covers
# POST, and an <img> or a link to its GET address on another page can no longer rate in the visitor's name. The shipped
# script (ezstarrating_jquery.js, jQuery.ez() with a post object) posts. A theme of its own that still calls the GET
# address keeps working with enabled here until it posts; enabled gives up that protection.
AllowGetRating=disabled

*/ ?>