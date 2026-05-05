describe('Solution Types CRUD', () => {
    let solutionTypes = [];

    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    it(`Check solution type default calculator layout (Default)`, () => {
        cy.visit('/');
        cy.get('#datatable-search').type('Mobile');
        cy.get('#datatable-btn-0-show').click();

        cy.get('.commission-calculator')
            .eq(0)
            .within(() => {
                cy.get('#margin-percentage').should('exist');
                cy.get('#margin-value').should('exist');
                cy.get('#commission-percentage').should('exist');
                cy.get('#commission-value').should('exist');
                cy.get('#total-value').should('exist');
            });
    });

    it(`Update solution type calculator layouts to Show Margin only`, () => {
        cy.visit('/tenants');
        cy.get('#1-1').click();

        cy.get('#solution-type-calculator-layout').click();
        cy.wait(500);
        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-multiselect-option').contains('Show Margin').click();
            });

        cy.wait(1000);

        cy.visit('/');
        cy.get('#datatable-search').type('Mobile');
        cy.get('#datatable-btn-0-show').click();

        cy.get('.commission-calculator')
            .eq(0)
            .within(() => {
                cy.get('#margin-percentage').should('exist');
                cy.get('#margin-value').should('exist');
                cy.get('#commission-percentage').should('not.exist');
                cy.get('#commission-value').should('not.exist');
                cy.get('#total-value').should('not.exist');
            });
    });

    it(`Update solution type calculator layouts to Show Commision only`, () => {
        cy.visit('/tenants');
        cy.get('#1-1').click();

        cy.get('#solution-type-calculator-layout').click();
        cy.wait(500);
        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-multiselect-option').contains('Show Margin').click();
                cy.get('.p-multiselect-option').contains('Show Commission').click();
            });

        cy.wait(1000);

        cy.visit('/');
        cy.get('#datatable-search').type('Mobile');
        cy.get('#datatable-btn-0-show').click();

        cy.get('.commission-calculator')
            .eq(0)
            .within(() => {
                cy.get('#margin-percentage').should('not.exist');
                cy.get('#margin-value').should('not.exist');
                cy.get('#commission-percentage').should('exist');
                cy.get('#commission-value').should('exist');
                cy.get('#total-value').should('not.exist');
            });
    });

    it(`Update solution type calculator layouts to Show Contract Total only`, () => {
        cy.visit('/tenants');
        cy.get('#1-1').click();

        cy.get('#solution-type-calculator-layout').click();
        cy.wait(500);
        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-multiselect-option').contains('Show Commission').click();
                cy.get('.p-multiselect-option').contains('Show Contract Total').click();
            });

        cy.wait(1000);

        cy.visit('/');
        cy.get('#datatable-search').type('Mobile');
        cy.get('#datatable-btn-0-show').click();

        cy.get('.commission-calculator')
            .eq(0)
            .within(() => {
                cy.get('#margin-percentage').should('not.exist');
                cy.get('#margin-value').should('not.exist');
                cy.get('#commission-percentage').should('not.exist');
                cy.get('#commission-value').should('not.exist');
                cy.get('#total-value').should('exist');
            });
    });

    it(`Update solution type calculator layouts to show all`, () => {
        cy.visit('/tenants');
        cy.get('#1-1').click();

        cy.get('#solution-type-calculator-layout').click();
        cy.wait(500);
        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-multiselect-option').contains('Show Margin').click();
                cy.get('.p-multiselect-option').contains('Show Commission').click();
            });

        cy.wait(1000);

        cy.visit('/');
        cy.get('#datatable-search').type('Mobile');
        cy.get('#datatable-btn-0-show').click();

        cy.get('.commission-calculator')
            .eq(0)
            .within(() => {
                cy.get('#margin-percentage').should('exist');
                cy.get('#margin-value').should('exist');
                cy.get('#commission-percentage').should('exist');
                cy.get('#commission-value').should('exist');
                cy.get('#total-value').should('exist');
            });
    });
});
