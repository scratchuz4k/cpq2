describe('Solution Types CRUD', () => {
    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    it('Edit Connectivity Solution add custom table', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-solutions').click();

        cy.get('#datatable-search').type('Connectivity');
        cy.get('#datatable-btn-0-edit').click();
        cy.get('#2-2').click();

        cy.get('#has_custom_table').click();
        cy.get('#solution-type-table-group').click();
        cy.get('.p-select-list-container')
            .eq(0)
            .within(() => {
                cy.get('li[aria-label="Price Plan"]').should('exist').click();
            });

        cy.get('#solution-type-table-remove-columns').click();
        cy.get('.swal2-confirm').eq(0).click();
        cy.get('#solution-type-table-add-column').click();
        cy.get('#solution-type-table-add-column').click();
        cy.get('#solution-type-table-add-column').click();

        cy.get('#table-column-0-value').click();
        cy.get('.p-select-list-container')
            .eq(0)
            .within(() => {
                cy.get('li[aria-label="Name"]').should('exist').click();
            });
        cy.get('#table-column-0-name').type('Name');

        cy.get('#table-column-1-value').click();
        cy.get('.p-select-list-container')
            .eq(0)
            .within(() => {
                cy.get('li[aria-label="Quantity"]').should('exist').click();
            });
        cy.get('#table-column-1-name').type('Quantity');

        cy.get('#table-column-2-value').click();
        cy.get('.p-select-list-container')
            .eq(0)
            .within(() => {
                cy.get('li[aria-label="Total"]').should('exist').click();
            });
        cy.get('#table-column-2-name').type('Total');

        cy.get('#btn-save-solution-type').click();
        cy.get('.notyf').contains('Updated with Success');
    });

    it('Delete Connectivity Solution', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-solutions').click();

        cy.get('#datatable-search').type('Connectivity');

        cy.get('.p-datatable-tbody tr').contains('td', 'Connectivity').parent().find('button[id*="delete"]').click();

        cy.get('.swal2-confirm').eq(0).click();
        cy.get('.notyf').contains('Removed with success');
    });

    it('Create a Connectivity - Solution', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-solutions').click();
        cy.get('#solution-create-btn').click();

        cy.get('#solution_type').click();
        cy.get('.p-select-list-container')
            .eq(0)
            .within(() => {
                cy.get('li[aria-label="Connectivity"]').should('exist').click();
            });

        cy.get('#btn-save-solution-type').click();

        cy.get('.notyf').contains('Created with Success');

        cy.get('#datatable-search').type('Connectivity');
        cy.get('.p-datatable-table').contains('Connectivity');
    });
});
