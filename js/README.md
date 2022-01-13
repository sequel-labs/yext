answers.js pulled from https://assets.sitescdn.net/answers/answers.js and patched to fix an issue
with its url handling when a url already includes get parameters.

also see https://github.com/yext/answers-search-ui

to update:
  1. retrieve updated copy of answers.js from https://assets.sitescdn.net/answers/answers.js
  2. remove source map
  3. apply answers.patch (e.g.  patch < answers.patch)
  4. verify and commit update