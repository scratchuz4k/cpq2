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

        //Change tabs
        cy.get('#1-1').click();

        cy.get('#solution_type-component').click();
        cy.get('.p-select-list-container')
            .eq(0)
            .within(() => {
                cy.contains('li', 'Simple').should('exist').click();
            });

        cy.get('#btn-save-solution-type').click();

        cy.get('.notyf').contains('Created with Success');

        cy.get('#datatable-search').type('Hardware');
        cy.get('.p-datatable-table').contains('Hardware');
    });

    it('Edit Connectivity Solution', () => {
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-solutions').click();

        cy.get('#datatable-search').type('Hardware');
        cy.wait(1000);
        cy.get('#datatable-btn-0-edit').click();

        cy.get('#2-2').click();

        cy.get('#solution_type-component').click();
        cy.get('.p-select-list-container')
            .eq(0)
            .within(() => {
                cy.contains('li', 'DealMaker').should('exist').click();
            });

        cy.get('#btn-save-solution-type').click();
        cy.get('.notyf').contains('Updated with Success');

        cy.get('#datatable-search').type('Hardware');
        cy.get('.p-datatable-table').contains('Hardware');
    });

    it('Delete Connectivity Solution', () => {
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-solutions').click();

        cy.get('#datatable-search').type('Hardware');
        cy.wait(1000);

        cy.get('.p-datatable-tbody tr').contains('td', 'Hardware').parent().find('button[id*="delete"]').click();

        cy.get('.swal2-confirm').eq(0).click();
        cy.get('.notyf').contains('Removed with success');
    });
});
