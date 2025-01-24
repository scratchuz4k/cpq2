const dropzoneSelector = '.dropzone'

describe('Update tenant logo', () => {
    beforeEach(() => {
        cy.login('super@example.com', 'werwerwer')
        cy.visit('/')
        cy.get('#my-account').click()
        cy.get('#link-tenant').click()
        cy.get('#v-pills-logo-tab').click()
    })

    it('upload image', () => {
        cy.get('#v-pills-logo').find('.input-resource').click()

        // Create a virtual file in the test
        const fileName = 'exampleFile.png'
        const fileType = 'image/png'
        const fileContent = 'This is a simulated file content'

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

        cy.get('#library-container').find('.p-card').first().click()
        cy.get('.action-button-wrapper').find('.p-button').eq(1).click()

        cy.get('.notyf').contains('Logo uploaded with Success')
    })

    it('upload wrong format image', () => {
        cy.get('#v-pills-logo').find('img').click()

        // Create a virtual file in the test
        const fileName = 'exampleFile.webp'
        const fileType = 'image/webp'
        const fileContent = 'This is a simulated file content'

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

        cy.get('#library-container').should('not.contain', fileName);
    })

    it('Remove tenant logo', () => {
        cy.get('#v-pills-logo').find('#remove-file-button').click()
        cy.get('.notyf').contains('Logo uploaded with Success')
        cy.get('#v-pills-logo').find('.input-resource').should('exist')
    })
})