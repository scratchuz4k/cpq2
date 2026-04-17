describe('Add tenant links.cy', () => {
    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    it('Setup tenant header links and check', () => {
        cy.visit('/tenants');
        cy.get('#2-2').click();
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
        cy.get('#2-2').click();
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

    it('Setup tenant social links and check', () => {
        cy.visit('/tenants');
        cy.get('#2-2').click();
        cy.get('#v-pills-social-tab').click();
        cy.selectOption('#social-new-link-icon', 'Facebook');
        cy.get('#social-new-link-is_active').click();
        cy.get('#social-new-link-add').click();
        cy.get('.notyf').contains('Please enter a valid URL');
        cy.get('#social-new-link-url').type('facebook.com');
        cy.get('#social-new-link-add').click();
        cy.get('.notyf').contains('Please enter a valid URL');
        cy.get('#social-new-link-url').clear().type('https://facebook.com');
        cy.get('#social-new-link-add').click();
        cy.get('.notyf').contains('Content updated successfully');
        cy.wait(1000);
        cy.visit('/tenants');
        cy.get('.footer .social-link')
            .should('have.attr', 'href')
            .should('not.be.empty')
            .and('contain', 'https://facebook.com');
    });
});
