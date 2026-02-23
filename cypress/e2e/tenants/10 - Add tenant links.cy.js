describe('Add tenant links.cy', () => {
    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    it('Setup tenant header links and check', () => {
        cy.visit('/tenants');
        cy.get('#v-pills-header-tab').click();
        cy.get('#header-new-link-label').clear().type('Header Link');
        cy.get('#header-new-link-is_active').click();
        cy.get('#header-new-link-add').click();
        cy.get('.notyf').contains('Please enter a valid URL');
        cy.get('#header-new-link-url').type('https://header.example.com');
        cy.get('#header-new-link-add').click();
        cy.get('.notyf').contains('Content updated successfully');
        cy.wait(1000);
        cy.visit('/tenants');
        cy.get('#navbarSupportedContent .nav-link').contains('Header Link').should('exist');
    });

    it('Setup tenant footer links and check', () => {
        cy.visit('/tenants');
        cy.get('#v-pills-footer-tab').click();
        cy.get('#footer-new-link-label').clear().type('Footer Link');
        cy.get('#footer-new-link-is_active').click();
        cy.get('#footer-new-link-add').click();
        cy.get('.notyf').contains('Please enter a valid URL');
        cy.get('#footer-new-link-url').type('https://footer.example.com');
        cy.get('#footer-new-link-add').click();
        cy.get('.notyf').contains('Content updated successfully');
        cy.wait(1000);
        cy.visit('/tenants');
        cy.get('.footer .footer-link').contains('Footer Link').should('exist');
    });
});
