Rockets PHP Framework
=====================

This folder contains the Rockets PHP framework files. The PHP framework should be reusable,
providing the template engine and class library which enables you to jump start
web site development in a flash. Rockets should *not* contain code that is specific
to any one project - otherwise its reusability will suffer.

**Single Point of Access

- All requests should go through rockets FIRST, since the whole site runs on the Rockets PHP framework.
- Rockets knows only about one other package: pkg_**** - that contains the project files.
