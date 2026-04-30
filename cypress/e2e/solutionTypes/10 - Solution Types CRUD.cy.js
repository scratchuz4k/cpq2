describe('Solution Types CRUD', () => {
    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    it('Create a Hardware Solution', () => {
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-solutions').click();

        cy.get('#datatable-search').type('Hardware');
        cy.wait(1000);

        cy.get('#solution-type-table').then(($table) => {
            if ($table.text().includes('Hardware')) {
                cy.get('#datatable-btn-0-delete').click();

                cy.get('.swal2-confirm').eq(0).click();
                cy.get('.notyf').contains('Removed with success');
            }
        });

        cy.get('#solution-create-btn').click();
        cy.get('#solution_type').click();
        cy.get('.p-select-list-container')
            .eq(0)
            .within(() => {
                cy.contains('li', 'Hardware').should('exist').click();
            });
    });
});
