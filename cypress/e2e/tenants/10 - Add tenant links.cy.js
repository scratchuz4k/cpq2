describe('Update tenant logo', () => {
    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    it('Setup tenant header links and check', () => {
        cy.visit('/tenants');
        cy.get('#v-pills-header-tab').click();
        cy.get('#header-new-link-label').clear().type('This label is too long for the system');
        cy.get('#header-new-link-url').type('https://quotes.example.com');
        cy.get('#header-new-link-is_active').click();
        cy.get('#header-new-link-add').click();
        cy.get('.notyf').contains('Label must be less than 20 characters');

        cy.get('#tenant-new-link-label').clear().type('Quotes Link');
        cy.get('#tenant-new-link-add').click();
        cy.get('#btn-save-tenant').click();
        cy.visit('/tenants');
        cy.get('#navbarSupportedContent .nav-link').contains('Quotes Link').should('exist');
    });
});
