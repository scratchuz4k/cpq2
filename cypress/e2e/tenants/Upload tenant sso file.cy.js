/**/ const { slugify, generateHash, getCurrentDate } = require('../../support/utils.js');
const dropzoneSelector = '.dropzone';
const XXEContent = `<?xml version="1.0"?>
<!DOCTYPE data [
  <!ENTITY xxe SYSTEM "file:///var/www/html/.env">
]>
<root>
  <config>&xxe;</config>
</root>`;

const mockSAMLMetadataContent = `<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" entityID="https://saml.example.com/entityid" validUntil="2034-04-19T15:48:52.991Z">
  <md:IDPSSODescriptor WantAuthnRequestsSigned="true" protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
    <md:KeyDescriptor use="signing">
      <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
        <ds:X509Data>
          <ds:X509Certificate>MIIC4jCCAcoCCQC33wnybT5QZDANBgkqhkiG9w0BAQsFADAyMQswCQYDVQQGEwJV
SzEPMA0GA1UECgwGQm94eUhRMRIwEAYDVQQDDAlNb2NrIFNBTUwwIBcNMjIwMjI4
MjE0NjM4WhgPMzAyMTA3MDEyMTQ2MzhaMDIxCzAJBgNVBAYTAlVLMQ8wDQYDVQQK
DAZCb3h5SFExEjAQBgNVBAMMCU1vY2sgU0FNTDCCASIwDQYJKoZIhvcNAQEBBQAD
ggEPADCCAQoCggEBALGfYettMsct1T6tVUwTudNJH5Pnb9GGnkXi9Zw/e6x45DD0
RuRONbFlJ2T4RjAE/uG+AjXxXQ8o2SZfb9+GgmCHuTJFNgHoZ1nFVXCmb/Hg8Hpd
4vOAGXndixaReOiq3EH5XvpMjMkJ3+8+9VYMzMZOjkgQtAqO36eAFFfNKX7dTj3V
pwLkvz6/KFCq8OAwY+AUi4eZm5J57D31GzjHwfjH9WTeX0MyndmnNB1qV75qQR3b
2/W5sGHRv+9AarggJkF+ptUkXoLtVA51wcfYm6hILptpde5FQC8RWY1YrswBWAEZ
NfyrR4JeSweElNHg4NVOs4TwGjOPwWGqzTfgTlECAwEAATANBgkqhkiG9w0BAQsF
AAOCAQEAAYRlYflSXAWoZpFfwNiCQVE5d9zZ0DPzNdWhAybXcTyMf0z5mDf6FWBW
5Gyoi9u3EMEDnzLcJNkwJAAc39Apa4I2/tml+Jy29dk8bTyX6m93ngmCgdLh5Za4
khuU3AM3L63g7VexCuO7kwkjh/+LqdcIXsVGO6XDfu2QOs1Xpe9zIzLpwm/RNYeX
UjbSj5ce/jekpAw7qyVVL4xOyh8AtUW1ek3wIw1MJvEgEPt0d16oshWJpoS1OT8L
r/22SvYEo3EmSGdTVGgk3x3s+A0qWAqTcyjr7Q4s/GKYRFfomGwz0TZ4Iw1ZN99M
m0eo2USlSRTVl7QHRTuiuSThHpLKQQ==</ds:X509Certificate>
        </ds:X509Data>
      </ds:KeyInfo>
    </md:KeyDescriptor>
    <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress</md:NameIDFormat>
    <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="https://mocksaml.com/api/saml/sso"/>
    <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="https://mocksaml.com/api/saml/sso"/>
  </md:IDPSSODescriptor>
</md:EntityDescriptor>`;

const domain = 'example.com';
const temp = getCurrentDate();
const user = { name: slugify(temp), email: slugify(temp) + '@' + domain, password: generateHash(temp) };

describe('Update tenant SSO', () => {
    it('Create Users to login with SSO', () => {
        Cypress.session.clearAllSavedSessions();
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-tenant').click();
        cy.get('#v-pills-sso-tab').click();
        cy.get('#settings').click();
        cy.get('#link-users').click();
        cy.get('#link-to-create-user').click();

        cy.get('#user-name').type(user.name);
        cy.get('#user-email').type(user.email);
        cy.get('#user-password').type(user.password);
        cy.selectLazyOption('user-role', 'Sales Rep');
        cy.get('#btn-save-user').click();
        cy.get('#datatable-search').type(user.name);
        cy.get('#users-table').contains(user.name);
    });

    it('upload xml with xxe injection', () => {
        Cypress.session.clearAllSavedSessions();
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-tenant').click();
        cy.get('#v-pills-sso-tab').click();
        cy.get('#v-pills-sso').find('.input-resource').click();

        // Create a virtual file in the test
        const fileName = 'xxe_injection_file.xml';
        const fileType = 'text/xml';
        const fileContent = XXEContent;

        // Convert content to a Blob
        const blob = new Blob([fileContent], { type: fileType });
        const file = new File([blob], fileName, { type: fileType });

        // Create a DataTransfer object and add the file
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);

        // Trigger the drop event on the Dropzone element
        cy.get(dropzoneSelector).trigger('drop', {
            dataTransfer
        });

        cy.get('#library-container').should('contain', fileName);

        cy.get('#library-container').find('.p-card').first().click();
        cy.get('.action-button-wrapper').find('.p-button').eq(1).click();

        cy.get('.notyf').contains('File uploaded with Success');

        cy.get('textarea')
            .invoke('val')
            .then((textareaContent) => {
                expect(textareaContent).to.equal(fileContent);
            });
    });

    it('upload malformated xml file', () => {
        Cypress.session.clearAllSavedSessions();
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-tenant').click();
        cy.get('#v-pills-sso-tab').click();
        cy.get('#v-pills-sso').find('img').click();

        const fileName = 'malformated_file.xml';
        const fileType = 'text/xml';
        const fileContent = 'Teste';

        const blob = new Blob([fileContent], { type: fileType });
        const file = new File([blob], fileName, { type: fileType });

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);

        cy.get(dropzoneSelector).trigger('drop', {
            dataTransfer
        });

        cy.get('.notyf').contains('The file must contain a valid XML.');
    });

    it('Update text area content', () => {
        Cypress.session.clearAllSavedSessions();
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-tenant').click();
        cy.get('#v-pills-sso-tab').click();
        //submite malformated xml
        cy.get('textarea').clear().type('Invalid xml content');
        cy.get('#submit-sso-textarea').click();
        cy.get('.notyf').contains('The sso text area must contain a valid XML.');

        //submite valid xml
        cy.get('textarea').clear().type(XXEContent);
        cy.get('#submit-sso-textarea').click();
        cy.get('.notyf').contains('Content updated successfully');
    });

    it('Remove tenant SSO file', () => {
        Cypress.session.clearAllSavedSessions();
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-tenant').click();
        cy.get('#v-pills-sso-tab').click();
        cy.get('#v-pills-sso').find('#remove-file-button').click();
        cy.get('.notyf').contains('File deleted with Success');
        cy.get('#v-pills-sso').find('.input-resource').should('exist');
    });

    it('Try to Login with SSO without SSO provider', () => {
        cy.visit('/login/sso');
        cy.get('#email').type(user.email);
        cy.get('#btn-login').click();

        cy.get('.info-box-primary').contains('No SSO provider was found for your domain');
    });

    it('upload mock-saml-metadata xml file', () => {
        Cypress.session.clearAllSavedSessions();
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-tenant').click();
        cy.get('#v-pills-sso-tab').click();
        cy.get('#v-pills-sso').find('.input-resource').click();

        // Create a virtual file in the test
        const fileName = 'mock_saml_metadata_file.xml';
        const fileType = 'text/xml';
        const fileContent = mockSAMLMetadataContent;

        // Convert content to a Blob
        const blob = new Blob([fileContent], { type: fileType });
        const file = new File([blob], fileName, { type: fileType });

        // Create a DataTransfer object and add the file
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);

        // Trigger the drop event on the Dropzone element
        cy.get(dropzoneSelector).trigger('drop', {
            dataTransfer
        });

        cy.get('#library-container').should('contain', fileName);

        cy.get('#library-container').find('.p-card').first().click();
        cy.get('.action-button-wrapper').find('.p-button').eq(1).click();

        cy.get('.notyf').contains('File uploaded with Success');

        cy.get('textarea')
            .invoke('val')
            .then((textareaContent) => {
                expect(textareaContent).to.equal(fileContent);
            });
    });

    it('Try to login with SSO a user without SSO login permission', () => {
        cy.visit('/login/sso');

        cy.get('#email').type(user.email);
        cy.get('#btn-login').click();

        cy.get('#username').type(user.name);
        cy.get('#domain').select(domain);
        cy.get('#password').clear().type(user.password);

        cy.get('button').contains('Sign In').click();

        cy.get('.info-box-primary').contains(
            'We were unable to log into your account using SSO, please contact your administrator'
        );
    });

    it('Update User to add SSO Login', () => {
        Cypress.session.clearAllSavedSessions();
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-tenant').click();
        cy.get('#v-pills-sso-tab').click();
        cy.get('#settings').click();
        cy.get('#link-users').click();

        cy.get('#datatable-search').type(user.name);
        cy.wait(1000);
        cy.get('#datatable-btn-0-edit').click();

        cy.get('#user-sso').click();
        cy.get('#btn-save-user').click();

        cy.get('#users-table').contains(user.name);
        cy.get('tbody tr').find('.sso-login-active').should('exist');
    });

    it('Login with SSO', () => {
        cy.visit('/login/sso');
        cy.get('#email').type(user.email);
        cy.get('#btn-login').click();

        cy.wait(500);
        cy.get('#username').type(user.name);
        cy.get('#domain').select(domain);
        cy.get('#password').clear().type(user.password);

        cy.get('button').contains('Sign In').click();

        cy.get('#settings').contains('SETTINGS');
    });
});
