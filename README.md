# Akeneo Variant Group Rest Api Bundle

Add the missing variant group rest api endpoint for Akeneo > 1.7.


## Installation

Require this Bundle:

```
composer config repositories.ampersand-variant-group-rest-api-bundle vcs https://github.com/AmpersandHQ/akeneo-variant-group-rest-api.git
composer require ampersand/akeneo-variant-group-rest-api
```

Register it in your AppKernel.php:
```
    new Ampersand\Bundle\VariantGroupRestApiBundle\AmpersandVariantGroupRestApiBundle(),
```

Add this to your app/config/routing.yml:
```
ampersand_variant_group_rest_api:
    resource: "@AmpersandVariantGroupRestApiBundle/Resources/config/routing.yml"
    prefix: /api
```

And finally, clean your cache:

```
php app/console cache:clear
```

## Usage

### GET data
```
http://akeneo.local/api/rest/v1/variant-groups/akeneo_tshirt
```

### POST data
```
http://akeneo.local/api/rest/v1/variant-groups
```

### Sample Payload

This is how VG data looks like, for both GET and POST requests.
```
{
    "code": "akeneo_tshirt",
    "type": "VARIANT",
    "axes": [
        "clothing_size",
        "main_color",
        "secondary_color"
    ],
    "values": {
        "name": [
            {
                "locale": null,
                "scope": null,
                "data": "The Akeneo T-Shirt"
            }
        ],
        "description": [
            {
                "locale": "de_DE",
                "scope": "ecommerce",
                "data": null
            },
            {
                "locale": "de_DE",
                "scope": "mobile",
                "data": null
            },
            {
                "locale": "de_DE",
                "scope": "print",
                "data": null
            },
            {
                "locale": "en_US",
                "scope": "ecommerce",
                "data": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam ut nulla egestas, ullamcorper dui nec, faucibus erat. Sed pharetra posuere neque fringilla mollis. Nulla elementum massa porta, facilisis turpis vitae, venenatis libero. Aliquam sagittis nisl in tempor ornare. Aenean ut odio libero. Pellentesque sed purus at orci bibendum efficitur sit amet in lectus. Nunc condimentum ornare mauris, sed vehicula lacus vestibulum vel. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Praesent pharetra vitae risus a mattis. Pellentesque orci est, molestie eu arcu ut, dapibus ullamcorper purus. Morbi dictum non sapien quis volutpat. Duis nisi ligula, convallis pretium elit eu, lacinia varius neque. Suspendisse auctor quis magna et ullamcorper. Cras diam neque, accumsan sit amet faucibus malesuada, dapibus ut tellus."
            },
            {
                "locale": "en_US",
                "scope": "mobile",
                "data": null
            },
            {
                "locale": "en_US",
                "scope": "print",
                "data": null
            },
            {
                "locale": "fr_FR",
                "scope": "ecommerce",
                "data": null
            },
            {
                "locale": "fr_FR",
                "scope": "mobile",
                "data": null
            },
            {
                "locale": "fr_FR",
                "scope": "print",
                "data": null
            }
        ],
        "release_date": [
            {
                "locale": null,
                "scope": "ecommerce",
                "data": "08/18/2017"
            },
            {
                "locale": null,
                "scope": "mobile",
                "data": null
            },
            {
                "locale": null,
                "scope": "print",
                "data": null
            }
        ]
    },
    "labels": {
        "de_DE": "T-Shirts Akeneo",
        "en_US": "Akeneo T-Shirts",
        "fr_FR": "T-Shirts Akeneo"
    }
}
```