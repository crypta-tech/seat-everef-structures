# seat-everef-structures
A module for [SeAT](https://github.com/eveseat/seat) that will import structure data from everef.

[![Latest Stable Version](https://img.shields.io/packagist/v/crypta-tech/seat-everef-structures.svg?style=flat-square)]()
[![License](https://img.shields.io/badge/license-GPLv2-blue.svg?style=flat-square)](https://raw.githubusercontent.com/crypta-tech/seat-everef-structures/master/LICENSE)

## Installatation
See the SeAT Docs [here](https://eveseat.github.io/docs/community_packages/)


## Usage
Once installed, the artisan command `php artisan cryptatech:everef-structures:update` will pull in updates. 
This can be put into the scheduler on a 7-20 day cycle. There is no real benefit on being super frequent, but should be less than 30 days.