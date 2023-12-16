# Sitegeist.StoneTablet
## Form Export Extension for Neos.Fusion.Form and Sitegeist.PaperTiger

This package gives neos editors the option to bind export functionality to fusion forms and export them as an Excel file using a backend module.

!!! The package does not have any dependency to Sitegeist.PaperTiger but the forms that are assembled using this package are also covered.

### Authors & Sponsors

* Masoud Hedayati - hedayati@sitegeist.de

*The development and the public-releases of this package is generously sponsored by our employer http://www.sitegeist.de.*

## Installation

Sitegeist.StoneTablet is available via packagist run `composer require sitegeist/stonetablet` to install.

To create the required tables run `./flow doctrine:migrate`

We use semantic-versioning so every breaking change will increase the major-version number.

## Usage

Forms need to adjust their configuration in order to be able to register themselves as an export candidate.

## Configuration

In order to bind the functionality to a form the package adds the `Sitegeist.StoneTablet:Mixin.ExportableForm` to Neos cms.
Forms which inherit the mixin, possess some new properties to determine if and how a form must be exported.

```yaml
Acme.Demo:FusionOrPaperTigerForm:
  superTypes:
    'Sitegeist.StoneTablet:Mixin.ExportableForm': true
```

To exclude fields per default from the export you have to alter the setting `Sitegeist.StoneTablet.defaultExcludedFields`

```yaml
Sitegeist:
  StoneTablet:
    defaultExcludedFields: ['honeypotfield', 'defaultCaptcheField']
```

## Inspector

### Optional Export

By selecting the Exportable checkbox, the form is registered as an export candidate and after submition the form data are saved in the associated table in the database.

### Excluded Fields

"Excluded Fields" property contains an array of field names, which are not supposed to be registered in the database and consequently not appear in the export file.
Field names of submit button, Friendly Captcha and privacy policy are best cases to be excluded from the export.

### Upload Fields

Upload Fields are also included in the export. As a result the exported archive file contains an Excel file along with all upload fields in a separate directory.

## Export Backend Module

Form Export Management is the corresponding backend module to export submitted forms over a specific period of time.
Removed forms do not appear in the export list anymore but the data remains in the database.
