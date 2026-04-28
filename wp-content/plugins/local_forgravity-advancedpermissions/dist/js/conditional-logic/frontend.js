/*
 * ATTENTION: An "eval-source-map" devtool has been used.
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file with attached SourceMaps in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/js/conditional-logic/frontend.js":
/*!**********************************************!*\
  !*** ./src/js/conditional-logic/frontend.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./utils */ \"./src/js/conditional-logic/utils.js\");\n/**\n * Internal dependencies\n */\n\nconst {\n  endpoints\n} = window.permissions;\nlet currentUser;\nwindow.addEventListener('DOMContentLoaded', () => {\n  /**\n   * Determines if the current user role matches the custom Conditional Logic fields rule.\n   *\n   * @since 3.0\n   *\n   * @param {boolean} isMatch Does the target field’s value match with the rule value?\n   * @param {number}  formId  The ID of the form in use.\n   * @param {Object}  rule    The current rule object.\n   *\n   * @return {boolean} If the current user matches the conditional logic rule.\n   */\n  const isValueMatch = (isMatch, formId, rule) => {\n    if (rule.fieldId !== _utils__WEBPACK_IMPORTED_MODULE_0__.FIELD_ROLE) {\n      return isMatch;\n    }\n    if (currentUser === undefined) {\n      jQuery.get({\n        url: endpoints.user,\n        async: false,\n        success: response => {\n          currentUser = response;\n        },\n        error: () => {\n          currentUser = false;\n        }\n      });\n    }\n    if (!currentUser) {\n      return false;\n    }\n    if (rule.operator === 'is') {\n      return currentUser.roles.includes(rule.value);\n    } else if (rule.operator === 'isnot') {\n      return !currentUser.roles.includes(rule.value);\n    }\n    return false;\n  };\n  gform.addFilter('gform_is_value_match', isValueMatch);\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9zcmMvanMvY29uZGl0aW9uYWwtbG9naWMvZnJvbnRlbmQuanMuanMiLCJtYXBwaW5ncyI6Ijs7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUVBO0FBQUE7QUFBQTtBQUNBO0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFFQTtBQUNBO0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBRUE7QUFFQTtBQUNBO0FBQ0E7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBRUE7QUFDQTtBQUVBO0FBQ0EiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9mb3JncmF2aXR5LWFkdmFuY2VkcGVybWlzc2lvbnMvLi9zcmMvanMvY29uZGl0aW9uYWwtbG9naWMvZnJvbnRlbmQuanM/OGU2NiJdLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEludGVybmFsIGRlcGVuZGVuY2llc1xuICovXG5pbXBvcnQgeyBGSUVMRF9ST0xFIH0gZnJvbSAnLi91dGlscyc7XG5cbmNvbnN0IHsgZW5kcG9pbnRzIH0gPSB3aW5kb3cucGVybWlzc2lvbnM7XG5sZXQgY3VycmVudFVzZXI7XG5cbndpbmRvdy5hZGRFdmVudExpc3RlbmVyKCAnRE9NQ29udGVudExvYWRlZCcsICgpID0+IHtcblx0LyoqXG5cdCAqIERldGVybWluZXMgaWYgdGhlIGN1cnJlbnQgdXNlciByb2xlIG1hdGNoZXMgdGhlIGN1c3RvbSBDb25kaXRpb25hbCBMb2dpYyBmaWVsZHMgcnVsZS5cblx0ICpcblx0ICogQHNpbmNlIDMuMFxuXHQgKlxuXHQgKiBAcGFyYW0ge2Jvb2xlYW59IGlzTWF0Y2ggRG9lcyB0aGUgdGFyZ2V0IGZpZWxk4oCZcyB2YWx1ZSBtYXRjaCB3aXRoIHRoZSBydWxlIHZhbHVlP1xuXHQgKiBAcGFyYW0ge251bWJlcn0gIGZvcm1JZCAgVGhlIElEIG9mIHRoZSBmb3JtIGluIHVzZS5cblx0ICogQHBhcmFtIHtPYmplY3R9ICBydWxlICAgIFRoZSBjdXJyZW50IHJ1bGUgb2JqZWN0LlxuXHQgKlxuXHQgKiBAcmV0dXJuIHtib29sZWFufSBJZiB0aGUgY3VycmVudCB1c2VyIG1hdGNoZXMgdGhlIGNvbmRpdGlvbmFsIGxvZ2ljIHJ1bGUuXG5cdCAqL1xuXHRjb25zdCBpc1ZhbHVlTWF0Y2ggPSAoIGlzTWF0Y2gsIGZvcm1JZCwgcnVsZSApID0+IHtcblx0XHRpZiAoIHJ1bGUuZmllbGRJZCAhPT0gRklFTERfUk9MRSApIHtcblx0XHRcdHJldHVybiBpc01hdGNoO1xuXHRcdH1cblxuXHRcdGlmICggY3VycmVudFVzZXIgPT09IHVuZGVmaW5lZCApIHtcblx0XHRcdGpRdWVyeS5nZXQoXG5cdFx0XHRcdHtcblx0XHRcdFx0XHR1cmw6ICAgICBlbmRwb2ludHMudXNlcixcblx0XHRcdFx0XHRhc3luYzogICBmYWxzZSxcblx0XHRcdFx0XHRzdWNjZXNzOiAoIHJlc3BvbnNlICkgPT4ge1xuXHRcdFx0XHRcdFx0Y3VycmVudFVzZXIgPSByZXNwb25zZTtcblx0XHRcdFx0XHR9LFxuXHRcdFx0XHRcdGVycm9yOiAgICgpID0+IHtcblx0XHRcdFx0XHRcdGN1cnJlbnRVc2VyID0gZmFsc2U7XG5cdFx0XHRcdFx0fSxcblx0XHRcdFx0fSxcblx0XHRcdCk7XG5cdFx0fVxuXG5cdFx0aWYgKCAhIGN1cnJlbnRVc2VyICkge1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cblxuXHRcdGlmICggcnVsZS5vcGVyYXRvciA9PT0gJ2lzJyApIHtcblx0XHRcdHJldHVybiBjdXJyZW50VXNlci5yb2xlcy5pbmNsdWRlcyggcnVsZS52YWx1ZSApO1xuXHRcdH0gZWxzZSBpZiAoIHJ1bGUub3BlcmF0b3IgPT09ICdpc25vdCcgKSB7XG5cdFx0XHRyZXR1cm4gISBjdXJyZW50VXNlci5yb2xlcy5pbmNsdWRlcyggcnVsZS52YWx1ZSApO1xuXHRcdH1cblxuXHRcdHJldHVybiBmYWxzZTtcblx0fTtcblxuXHRnZm9ybS5hZGRGaWx0ZXIoICdnZm9ybV9pc192YWx1ZV9tYXRjaCcsIGlzVmFsdWVNYXRjaCApO1xufSApO1xuIl0sIm5hbWVzIjpbXSwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./src/js/conditional-logic/frontend.js\n");

/***/ }),

/***/ "./src/js/conditional-logic/utils.js":
/*!*******************************************!*\
  !*** ./src/js/conditional-logic/utils.js ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"FIELD_ROLE\": () => (/* binding */ FIELD_ROLE),\n/* harmony export */   \"conditionalLogicFields\": () => (/* binding */ conditionalLogicFields),\n/* harmony export */   \"conditionalLogicOperators\": () => (/* binding */ conditionalLogicOperators),\n/* harmony export */   \"getRolesAsOptions\": () => (/* binding */ getRolesAsOptions)\n/* harmony export */ });\n/**\n * WordPress dependencies\n */\nconst {\n  __\n} = wp.i18n;\n\n/**\n * Internal dependencies\n */\nconst {\n  roles\n} = window.permissions;\nconst FIELD_ROLE = 'advancedpermissions-role';\n\n/**\n * Add custom Conditional Logic fields.\n *\n * @since 3.0\n *\n * @param {Array} options An array consisting of each conditional logic field with its label and field id.\n *\n * @return {Array} Conditional logic fields.\n */\nconst conditionalLogicFields = options => {\n  options.push({\n    label: __('Current User Role', 'forgravity_advancedpermissions'),\n    value: FIELD_ROLE\n  });\n  return options;\n};\n\n/**\n * Filters the allowed operators for the custom Conditional Logic fields.\n *\n * @since 3.0\n *\n * @param {Object[]} operators  The current operators.\n * @param {string}   objectType The current conditional logic object type.\n * @param {string}   fieldId    The ID of the current field.\n *\n * @return {Object[]} Allowed conditional logic operators.\n */\nconst conditionalLogicOperators = (operators, objectType, fieldId) => {\n  if (fieldId !== FIELD_ROLE) {\n    return operators;\n  }\n  const allowed = ['is', 'isnot'];\n  return Object.fromEntries(Object.entries(operators).filter(([key]) => allowed.includes(key)));\n};\n\n/**\n * Returns the default WordPress roles as options.\n *\n * @since 3.0\n *\n * @return {{\"label\": string,\"value\": string}[]} Roles as a label/value pair.\n */\nconst getRolesAsOptions = () => {\n  return roles[0].options;\n};//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9zcmMvanMvY29uZGl0aW9uYWwtbG9naWMvdXRpbHMuanMuanMiLCJtYXBwaW5ncyI6Ijs7Ozs7OztBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBQUE7QUFBQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUFBO0FBQUE7QUFFQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBRUE7QUFDQTtBQUNBO0FBR0E7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFFQTtBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9mb3JncmF2aXR5LWFkdmFuY2VkcGVybWlzc2lvbnMvLi9zcmMvanMvY29uZGl0aW9uYWwtbG9naWMvdXRpbHMuanM/ZDZlNyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIFdvcmRQcmVzcyBkZXBlbmRlbmNpZXNcbiAqL1xuY29uc3QgeyBfXyB9ID0gd3AuaTE4bjtcblxuLyoqXG4gKiBJbnRlcm5hbCBkZXBlbmRlbmNpZXNcbiAqL1xuY29uc3QgeyByb2xlcyB9ID0gd2luZG93LnBlcm1pc3Npb25zO1xuXG5leHBvcnQgY29uc3QgRklFTERfUk9MRSA9ICdhZHZhbmNlZHBlcm1pc3Npb25zLXJvbGUnO1xuXG4vKipcbiAqIEFkZCBjdXN0b20gQ29uZGl0aW9uYWwgTG9naWMgZmllbGRzLlxuICpcbiAqIEBzaW5jZSAzLjBcbiAqXG4gKiBAcGFyYW0ge0FycmF5fSBvcHRpb25zIEFuIGFycmF5IGNvbnNpc3Rpbmcgb2YgZWFjaCBjb25kaXRpb25hbCBsb2dpYyBmaWVsZCB3aXRoIGl0cyBsYWJlbCBhbmQgZmllbGQgaWQuXG4gKlxuICogQHJldHVybiB7QXJyYXl9IENvbmRpdGlvbmFsIGxvZ2ljIGZpZWxkcy5cbiAqL1xuZXhwb3J0IGNvbnN0IGNvbmRpdGlvbmFsTG9naWNGaWVsZHMgPSAoIG9wdGlvbnMgKSA9PiB7XG5cdG9wdGlvbnMucHVzaChcblx0XHR7XG5cdFx0XHRsYWJlbDogX18oICdDdXJyZW50IFVzZXIgUm9sZScsICdmb3JncmF2aXR5X2FkdmFuY2VkcGVybWlzc2lvbnMnICksXG5cdFx0XHR2YWx1ZTogRklFTERfUk9MRSxcblx0XHR9LFxuXHQpO1xuXG5cdHJldHVybiBvcHRpb25zO1xufTtcblxuLyoqXG4gKiBGaWx0ZXJzIHRoZSBhbGxvd2VkIG9wZXJhdG9ycyBmb3IgdGhlIGN1c3RvbSBDb25kaXRpb25hbCBMb2dpYyBmaWVsZHMuXG4gKlxuICogQHNpbmNlIDMuMFxuICpcbiAqIEBwYXJhbSB7T2JqZWN0W119IG9wZXJhdG9ycyAgVGhlIGN1cnJlbnQgb3BlcmF0b3JzLlxuICogQHBhcmFtIHtzdHJpbmd9ICAgb2JqZWN0VHlwZSBUaGUgY3VycmVudCBjb25kaXRpb25hbCBsb2dpYyBvYmplY3QgdHlwZS5cbiAqIEBwYXJhbSB7c3RyaW5nfSAgIGZpZWxkSWQgICAgVGhlIElEIG9mIHRoZSBjdXJyZW50IGZpZWxkLlxuICpcbiAqIEByZXR1cm4ge09iamVjdFtdfSBBbGxvd2VkIGNvbmRpdGlvbmFsIGxvZ2ljIG9wZXJhdG9ycy5cbiAqL1xuZXhwb3J0IGNvbnN0IGNvbmRpdGlvbmFsTG9naWNPcGVyYXRvcnMgPSAoIG9wZXJhdG9ycywgb2JqZWN0VHlwZSwgZmllbGRJZCApID0+IHtcblx0aWYgKCBmaWVsZElkICE9PSBGSUVMRF9ST0xFICkge1xuXHRcdHJldHVybiBvcGVyYXRvcnM7XG5cdH1cblxuXHRjb25zdCBhbGxvd2VkID0gWyAnaXMnLCAnaXNub3QnIF07XG5cblx0cmV0dXJuIE9iamVjdC5mcm9tRW50cmllcyggT2JqZWN0LmVudHJpZXMoIG9wZXJhdG9ycyApLmZpbHRlciggKCBbIGtleSBdICkgPT4gYWxsb3dlZC5pbmNsdWRlcygga2V5ICkgKSApO1xufTtcblxuLyoqXG4gKiBSZXR1cm5zIHRoZSBkZWZhdWx0IFdvcmRQcmVzcyByb2xlcyBhcyBvcHRpb25zLlxuICpcbiAqIEBzaW5jZSAzLjBcbiAqXG4gKiBAcmV0dXJuIHt7XCJsYWJlbFwiOiBzdHJpbmcsXCJ2YWx1ZVwiOiBzdHJpbmd9W119IFJvbGVzIGFzIGEgbGFiZWwvdmFsdWUgcGFpci5cbiAqL1xuZXhwb3J0IGNvbnN0IGdldFJvbGVzQXNPcHRpb25zID0gKCkgPT4ge1xuXHRyZXR1cm4gcm9sZXNbIDAgXS5vcHRpb25zO1xufTtcbiJdLCJuYW1lcyI6W10sInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///./src/js/conditional-logic/utils.js\n");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval-source-map devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./src/js/conditional-logic/frontend.js");
/******/ 	
/******/ })()
;