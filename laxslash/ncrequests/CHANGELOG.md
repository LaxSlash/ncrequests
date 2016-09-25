Version 0.4 ALPHA:
- Fixed the get_redirect_url() function to actually return something in ncr_edit.
- Fixed PHP Notices in the ACP and UCP Modules.
- Fixed PHP File comment headers (This is NCRequests, not Force Styles)
- A few minor lingual corrections, changes and clarifications
- Added log viewing to the ACP and UCP modules
- Fixed no new-line in at least one file
- Fixed services.yml spacing
- Notifications can no longer be ignored by the user
- SQL Query Changes to accomodate for the new logging system

Version 0.3 ALPHA
- Updated Version 0.2 ALPHA Changelog Entry
- array_intersect_key is now used for getting last approval dates on applicable entries on the ACP Manage Page/Module
- Notifications item_id for approval/denials is now equal to the request_id, Parent ID set to 0. Edit requests item_id is now set to the laxslash_ncrequests_notification_id
- New config variable added: laxslash_ncrequests_notification_id
- Fixed an issue in previous migrations files that would prevent a new installation from taking place after a Data Delete.
- Fixed a PHP Array to String Notice in the notifications files
- Fixed incorrect variables in ncr_approval.php
- Fixed multiple undefined array/variable errors in the ACP Module.

Version 0.2 ALPHA:
- Fixed an SQL Error in the UCP Module
- Added back links and meta_redirects to the UCP
- Fixed ACP module back link functions (Function name must be a string errors)

Version 0.1-PL1 ALPHA:
- Removed notification/ncrequest_status.php
- Added extra blank/whitespace line at the end of all files where one was still needed.
- Added a changelog.
- Fixed SQL Error in manage_requests_module.php on Approve
- Updated ACP Module Version

Version 0.1 ALPHA:
- Initial Release