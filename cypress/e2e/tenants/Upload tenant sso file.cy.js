const dropzoneSelector = '.dropzone';
const xmlValidContent = `<?xml version="1.0" encoding="UTF-8"?>
    <root>
        <child>Example content</child>
    </root>`;

describe('Update tenant SSO', () => {
    beforeEach(() => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-tenant').click();
        cy.get('#v-pills-sso-tab').click();
    });

    it('upload xml', () => {
        cy.get('#v-pills-sso').find('.input-resource').click();

        // Create a virtual file in the test
        const fileName = 'exampleFile.xml';
        const fileType = 'text/xml';
        const fileContent = xmlValidContent;

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
        cy.get('#v-pills-sso').find('img').click();

        const fileName = 'exampleFile.xml';
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
        //submite malformated xml
        cy.get('textarea').clear().type('Invalid xml content');
        cy.get('#submit-sso-textarea').click();
        cy.get('.notyf').contains('The sso text area must contain a valid XML.');

        //submite valis xml
        cy.get('textarea').clear().type(xmlValidContent);
        cy.get('#submit-sso-textarea').click();
        cy.get('.notyf').contains('Content updated successfully');
    });

    it('Remove tenant SSO file', () => {
        cy.get('#v-pills-sso').find('#remove-file-button').click();
        cy.get('.notyf').contains('File deleted with Success');
        cy.get('#v-pills-sso').find('.input-resource').should('exist');
    });
});
