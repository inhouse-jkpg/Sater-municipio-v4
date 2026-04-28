## Version 3.1 (2023-11-28)
- Added compatibility with PHP 8.2.
- Added security enhancements.
- Fixed an issue where forms whose title contain non-Latin characters could not add new rules.
- Fixed an issue where the Add New Form button would appear in some scenarios.
- Removed unused Guzzle dependency.

## Version 3.0 (2023-07-05)
- Added compatibility with WordPress 6.2.
- Added Form Permissions defaults.
- Added Entry Permissions for setting what entries specific users/roles can access on a form.
- Added support for setting conditional logic based on current user's role.
- Added support for toggling status for multiple capabilities at once.
- Updated API URL to CosmicGiant.com.
- Updated Form Permissions UI to match new Gravity Forms design standards.
- Fixed an issue where enabling Form Settings related capabilities does not display dropdown menu.
- Fixed an issue where Forms List filter counts did not exclude forms user does not have access to.
- Fixed an issue where Super Admins could not see the Permissions form settings tab on child sites.
- Fixed an issue where user could not access form if an empty rule existed.
- Fixed an issue where users could not be found when searching with uppercase letters.
- Fixed an issue where users who should not have access could see the Import/Export page.

## Version 2.0 (2021-08-16)
- Added support for users who do not have Gravity Forms access.
- Added ruleset duplication when duplicating form.
- Fixed an issue where multiple capabilities would toggle at once if they shared the same name.
- Fixed forms user does not have access to appearing in Admin Bar.
- Fixed forms user does not have access to appearing in Gravity Forms block.

## Version 1.3 (2021-05-28)
- Fixed PHP fatal errors thrown when activating the Add-On in some scenarios.
- Fixed an issue where forms could be set as inactive upon creation.

## Version 1.2 (2021-05-19)
- Added a new API route to get users.
- Added compatibility with Gravity Forms 2.5.
- Added support for setting auto-updates state on Plugins page.
- Added the current user ID to the Form object when a form is created.
- Fixed Entries sub-menu page displaying an error when first form does not have access to entries.

## Version 1.1 (2020-02-11)
- Added compatibility with Gravity Forms 2.4.15.
- Added support for Export Entries capability.
- Added support for hiding forms user does not have access to from Dashboard widget. (Requires Gravity Forms 2.4.16+)
- Added support for hiding forms user does not have access to from Form Switcher. (Requires Gravity Forms 2.4.16+)
- Added support for ruleset operators (is/is not).
- Updated forms list to hide forms user has no access to.
- Fixed an issue where capabilities were not applying early in some scenarios.
- Fixed an issue where permissions could not be retrieved when WordPress was not installed at top level directory.
- Fixed an issue where permissions could not be retrieved when WordPress was using plain permalinks.
- Fixed an issue where permissions would not apply when going to Entries page from menu item.
- Fixed an issue with Preview Form toolbar item being incorrectly removed.
- Fixed forms with denied access from appearing in Gravity Flow Status filter.
- Fixed users with Edit Form Settings capability disabled being able to deactivate forms.

## Version 1.0 (2019-04-29)
- It's all new!
