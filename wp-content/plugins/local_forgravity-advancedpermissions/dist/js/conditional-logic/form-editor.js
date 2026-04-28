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

/***/ "./src/js/conditional-logic/form-editor.js":
/*!*************************************************!*\
  !*** ./src/js/conditional-logic/form-editor.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./utils */ \"./src/js/conditional-logic/utils.js\");\nfunction ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }\nfunction _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }\nfunction _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }\nfunction _toPropertyKey(arg) { var key = _toPrimitive(arg, \"string\"); return typeof key === \"symbol\" ? key : String(key); }\nfunction _toPrimitive(input, hint) { if (typeof input !== \"object\" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || \"default\"); if (typeof res !== \"object\") return res; throw new TypeError(\"@@toPrimitive must return a primitive value.\"); } return (hint === \"string\" ? String : Number)(input); }\n/**\n * Internal dependencies\n */\n\nwindow.addEventListener('DOMContentLoaded', () => {\n  /**\n   * Add drop down options for custom Conditional Logic fields.\n   *\n   * @since 3.0\n   *\n   * @param {string} html       String of the HTML input tag.\n   * @param {string} objectType The type of object: page, field, next_button, confirmation, notification.\n   * @param {number} ruleIndex  The index of the rule. The first rule is indexed at 0.\n   * @param {string} fieldId    The ID of the field chosen for comparison.\n   * @param {string} value      The value used for comparison.\n   *\n   * @return {string} HTML string of the conditional logic value input.\n   */\n  const conditionalLogicValuesInput = (html, objectType, ruleIndex, fieldId, value) => {\n    if (fieldId !== _utils__WEBPACK_IMPORTED_MODULE_0__.FIELD_ROLE) {\n      return html;\n    }\n    let optionsHTML = '';\n    (0,_utils__WEBPACK_IMPORTED_MODULE_0__.getRolesAsOptions)().forEach(role => {\n      const choiceConfig = _objectSpread(_objectSpread({}, role), {}, {\n        selected: role.value === value ? 'selected=\"selected\"' : ''\n      });\n      optionsHTML += renderView(window.gf_vars.conditionalLogic.views.option, null, choiceConfig, false);\n    });\n    const config = {\n      ruleIndex,\n      fieldValueOptions: optionsHTML\n    };\n    return renderView(window.gf_vars.conditionalLogic.views.select, null, config, false);\n  };\n  gform.addFilter('gform_conditional_logic_fields', _utils__WEBPACK_IMPORTED_MODULE_0__.conditionalLogicFields);\n  gform.addFilter('gform_conditional_logic_operators', _utils__WEBPACK_IMPORTED_MODULE_0__.conditionalLogicOperators);\n  gform.addFilter('gform_conditional_logic_values_input', conditionalLogicValuesInput);\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9zcmMvanMvY29uZGl0aW9uYWwtbG9naWMvZm9ybS1lZGl0b3IuanMuanMiLCJtYXBwaW5ncyI6Ijs7Ozs7OztBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBRUE7QUFFQTtBQUVBO0FBRUE7QUFBQTtBQUdBO0FBQ0E7QUFHQTtBQUNBO0FBQ0E7QUFDQTtBQUVBO0FBQ0E7QUFFQTtBQUNBO0FBQ0E7QUFDQSIsInNvdXJjZXMiOlsid2VicGFjazovL2ZvcmdyYXZpdHktYWR2YW5jZWRwZXJtaXNzaW9ucy8uL3NyYy9qcy9jb25kaXRpb25hbC1sb2dpYy9mb3JtLWVkaXRvci5qcz9iMjI3Il0sInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogSW50ZXJuYWwgZGVwZW5kZW5jaWVzXG4gKi9cbmltcG9ydCB7IEZJRUxEX1JPTEUsIGNvbmRpdGlvbmFsTG9naWNGaWVsZHMsIGNvbmRpdGlvbmFsTG9naWNPcGVyYXRvcnMsIGdldFJvbGVzQXNPcHRpb25zIH0gZnJvbSAnLi91dGlscyc7XG5cbndpbmRvdy5hZGRFdmVudExpc3RlbmVyKCAnRE9NQ29udGVudExvYWRlZCcsICgpID0+IHtcblx0LyoqXG5cdCAqIEFkZCBkcm9wIGRvd24gb3B0aW9ucyBmb3IgY3VzdG9tIENvbmRpdGlvbmFsIExvZ2ljIGZpZWxkcy5cblx0ICpcblx0ICogQHNpbmNlIDMuMFxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gaHRtbCAgICAgICBTdHJpbmcgb2YgdGhlIEhUTUwgaW5wdXQgdGFnLlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gb2JqZWN0VHlwZSBUaGUgdHlwZSBvZiBvYmplY3Q6IHBhZ2UsIGZpZWxkLCBuZXh0X2J1dHRvbiwgY29uZmlybWF0aW9uLCBub3RpZmljYXRpb24uXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSBydWxlSW5kZXggIFRoZSBpbmRleCBvZiB0aGUgcnVsZS4gVGhlIGZpcnN0IHJ1bGUgaXMgaW5kZXhlZCBhdCAwLlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gZmllbGRJZCAgICBUaGUgSUQgb2YgdGhlIGZpZWxkIGNob3NlbiBmb3IgY29tcGFyaXNvbi5cblx0ICogQHBhcmFtIHtzdHJpbmd9IHZhbHVlICAgICAgVGhlIHZhbHVlIHVzZWQgZm9yIGNvbXBhcmlzb24uXG5cdCAqXG5cdCAqIEByZXR1cm4ge3N0cmluZ30gSFRNTCBzdHJpbmcgb2YgdGhlIGNvbmRpdGlvbmFsIGxvZ2ljIHZhbHVlIGlucHV0LlxuXHQgKi9cblx0Y29uc3QgY29uZGl0aW9uYWxMb2dpY1ZhbHVlc0lucHV0ID0gKCBodG1sLCBvYmplY3RUeXBlLCBydWxlSW5kZXgsIGZpZWxkSWQsIHZhbHVlICkgPT4ge1xuXHRcdGlmICggZmllbGRJZCAhPT0gRklFTERfUk9MRSApIHtcblx0XHRcdHJldHVybiBodG1sO1xuXHRcdH1cblxuXHRcdGxldCBvcHRpb25zSFRNTCA9ICcnO1xuXG5cdFx0Z2V0Um9sZXNBc09wdGlvbnMoKS5mb3JFYWNoKFxuXHRcdFx0KCByb2xlICkgPT4ge1xuXHRcdFx0XHRjb25zdCBjaG9pY2VDb25maWcgPSB7XG5cdFx0XHRcdFx0Li4ucm9sZSxcblx0XHRcdFx0XHRzZWxlY3RlZDogcm9sZS52YWx1ZSA9PT0gdmFsdWUgPyAnc2VsZWN0ZWQ9XCJzZWxlY3RlZFwiJyA6ICcnLFxuXHRcdFx0XHR9O1xuXG5cdFx0XHRcdG9wdGlvbnNIVE1MICs9IHJlbmRlclZpZXcoIHdpbmRvdy5nZl92YXJzLmNvbmRpdGlvbmFsTG9naWMudmlld3Mub3B0aW9uLCBudWxsLCBjaG9pY2VDb25maWcsIGZhbHNlICk7XG5cdFx0XHR9LFxuXHRcdCk7XG5cblx0XHRjb25zdCBjb25maWcgPSB7XG5cdFx0XHRydWxlSW5kZXgsXG5cdFx0XHRmaWVsZFZhbHVlT3B0aW9uczogb3B0aW9uc0hUTUwsXG5cdFx0fTtcblxuXHRcdHJldHVybiByZW5kZXJWaWV3KCB3aW5kb3cuZ2ZfdmFycy5jb25kaXRpb25hbExvZ2ljLnZpZXdzLnNlbGVjdCwgbnVsbCwgY29uZmlnLCBmYWxzZSApO1xuXHR9O1xuXG5cdGdmb3JtLmFkZEZpbHRlciggJ2dmb3JtX2NvbmRpdGlvbmFsX2xvZ2ljX2ZpZWxkcycsIGNvbmRpdGlvbmFsTG9naWNGaWVsZHMgKTtcblx0Z2Zvcm0uYWRkRmlsdGVyKCAnZ2Zvcm1fY29uZGl0aW9uYWxfbG9naWNfb3BlcmF0b3JzJywgY29uZGl0aW9uYWxMb2dpY09wZXJhdG9ycyApO1xuXHRnZm9ybS5hZGRGaWx0ZXIoICdnZm9ybV9jb25kaXRpb25hbF9sb2dpY192YWx1ZXNfaW5wdXQnLCBjb25kaXRpb25hbExvZ2ljVmFsdWVzSW5wdXQgKTtcbn0gKTtcbiJdLCJuYW1lcyI6W10sInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///./src/js/conditional-logic/form-editor.js\n");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./src/js/conditional-logic/form-editor.js");
/******/ 	
/******/ })()
;