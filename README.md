bundle-generator
================

A Symfony2 bundle generation script meant to be used by composer

## Usage

      composer create-project pgmillon/symfony-bundle MyBundle

A post-create-project-cmd is configured to call the buildClasses method of the ScriptHandler class

The script will prompt for the vendor and bundle names a will generate the bundle files accordingly
