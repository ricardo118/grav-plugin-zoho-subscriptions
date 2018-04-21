---
title: PLUGIN_ZOHO_SUBSCRIPTIONS.CLIENT_PORTAL
template: zoho_portal
body_classes: zoho_portal
access:
    admin.zoho_subscriptions: true
    admin.super: true
form:
        name: edit-details
        refresh_prevention: true
        fields:
            spacer: 
                title: PLUGIN_ZOHO_SUBSCRIPTIONS.USER_DETAILS
                type: spacer
            display_name:
                label: PLUGIN_ZOHO_SUBSCRIPTIONS.DISPLAY_NAME
                data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefault', 'display_name']
                type: text
            salutation: 
                type: select
                size: long
                classes: fancy
                label: PLUGIN_ZOHO_SUBSCRIPTIONS.SALUTATION
                data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefault', 'salutation']
                options:
                    Mr: PLUGIN_ZOHO_SUBSCRIPTIONS.MR
                    Mrs: PLUGIN_ZOHO_SUBSCRIPTIONS.MRS
                    Ms: PLUGIN_ZOHO_SUBSCRIPTIONS.MS
                    Miss: PLUGIN_ZOHO_SUBSCRIPTIONS.MISS
            first_name: 
                label: PLUGIN_ZOHO_SUBSCRIPTIONS.FIRST_NAME
                data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefault', 'first_name']
                type: text 
            last_name: 
                label: PLUGIN_ZOHO_SUBSCRIPTIONS.LAST_NAME
                data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefault', 'last_name']
                type: text 
            email: 
                label: PLUGIN_ZOHO_SUBSCRIPTIONS.EMAIL
                data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefault', 'email']
                type: email 
            company_name: 
                label: PLUGIN_ZOHO_SUBSCRIPTIONS.COMPANY_NAME
                data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefault', 'company_name']
                type: text 
            phone: 
                label: PLUGIN_ZOHO_SUBSCRIPTIONS.PHONE
                data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefault', 'phone']
                type: text 
                validate:
                    type: number
            mobile: 
                label: PLUGIN_ZOHO_SUBSCRIPTIONS.MOBILE
                data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefault', 'mobile']
                type: text 
                validate:
                    type: number
            spacer2: 
                title: PLUGIN_ZOHO_SUBSCRIPTIONS.DEFAULT_ADDRESS
                type: spacer 
            billing_address: 
                type: hidden
            billing_address.street: 
                label: PLUGIN_ZOHO_SUBSCRIPTIONS.STREET
                data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefaultAddress', 'street']
                type: text 
            billing_address.city: 
                label: PLUGIN_ZOHO_SUBSCRIPTIONS.CITY
                data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefaultAddress', 'city']
                type: text 
            billing_address.state: 
                label: PLUGIN_ZOHO_SUBSCRIPTIONS.STATE
                data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefaultAddress', 'state']
                type: text 
            billing_address.zip: 
                label: PLUGIN_ZOHO_SUBSCRIPTIONS.ZIP
                data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefaultAddress', 'zip']
                type: text 
            billing_address.country: 
                label: PLUGIN_ZOHO_SUBSCRIPTIONS.COUNTRY
                data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefaultAddress', 'country']
                type: text               
        process:
            - edit_details: 'zoho-portal'
        buttons:
            - type: submit
              classes: button primary
              value: PLUGIN_ZOHO_SUBSCRIPTIONS.SUBMIT      
cardform:
    name: edit-card
    refresh_prevention: true
    fields:
        spacer: 
            title: PLUGIN_ZOHO_SUBSCRIPTIONS.CARD_DETAILS
            type: spacer
        card_number:
            label: PLUGIN_ZOHO_SUBSCRIPTIONS.CARD_NUMBER
            type: text
            validate:
                required: true
                type: number
        expiry_month: 
            label: PLUGIN_ZOHO_SUBSCRIPTIONS.EXPIRY_MONTH
            type: text 
            validate:
                required: true
                type: number
        expiry_year: 
            label: PLUGIN_ZOHO_SUBSCRIPTIONS.EXPIRY_YEAR
            type: text 
            validate:
                required: true
                type: number
        cvv_number: 
            label: PLUGIN_ZOHO_SUBSCRIPTIONS.CVV
            type: text 
            validate:
                required: true
                type: number
        first_name: 
            label: PLUGIN_ZOHO_SUBSCRIPTIONS.FIRST_NAME
            data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefault', 'first_name']
            type: text 
            validate:
                required: true
        last_name: 
            label: PLUGIN_ZOHO_SUBSCRIPTIONS.LAST_NAME
            data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefault', 'last_name']
            type: text 
            validate:
                required: true
        spacer2: 
            title: PLUGIN_ZOHO_SUBSCRIPTIONS.DEFAULT_ADDRESS
            type: spacer 
        street: 
            label: PLUGIN_ZOHO_SUBSCRIPTIONS.STREET
            data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefaultAddress', 'street']
            type: text 
            validate:
                required: true
        city: 
            label: PLUGIN_ZOHO_SUBSCRIPTIONS.CITY
            data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefaultAddress', 'city']
            type: text 
            validate:
                required: true
        state: 
            label: PLUGIN_ZOHO_SUBSCRIPTIONS.STATE
            data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefaultAddress', 'state']
            type: text 
            validate:
                required: true
        zip: 
            label: PLUGIN_ZOHO_SUBSCRIPTIONS.ZIP
            data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefaultAddress', 'zip']
            type: text 
            validate:
                required: true
        country: 
            label: PLUGIN_ZOHO_SUBSCRIPTIONS.COUNTRY
            data-default@: ['\Grav\Plugin\ZohoSubscriptionsPlugin::getMyDefaultAddress', 'country']
            type: text   
            validate:
                required: true            
    process:
        - card_details: 'card_details'
    buttons:
        - type: submit
          classes: button primary
          value: PLUGIN_ZOHO_SUBSCRIPTIONS.SUBMIT
              
---
