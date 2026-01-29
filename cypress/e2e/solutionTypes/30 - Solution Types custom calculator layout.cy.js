describe('Solution Types CRUD', () => {
    let solutionTypes = [];

    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    before('Get Solution Types', () => {
        cy.login('companyadmin@example.com', 'werwerwer');
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-solutions').click();

        cy.get('.p-datatable-tbody tr').not('.p-datatable-empty-message').should('have.length.greaterThan', 0);

        cy.get('.p-datatable-thead th').then(($headers) => {
            const colIndex = [...$headers].findIndex((h) => h.innerText.trim() === 'Name');

            // now get the cells from that column
            cy.get(`.p-datatable-tbody tr td:nth-child(${colIndex + 1})`)
                .not('.p-datatable-emptymessage td')
                .then(($cells) => {
                    solutionTypes = [...$cells].map((cell) => cell.innerText.trim());
                });
        });
    });

    solutionTypes.forEach((solutionType) => {
        it(`Check ${solutionType} solution type default calculator layout`, () => {
            cy.visit('/');
            cy.get('#datatable-search').type(type);
            cy.get('#datatable-btn-0-show').click();

            cy.get('.commission-calculator')
                .eq(0)
                .within(() => {
                    cy.get('#commission_text').should('exist');
                    cy.get('#margin_text').should('exist');
                    cy.get('#input-commission_value-order').should('exist');
                    cy.get('#input-margin-order').should('exist');
                });
        });

        it(`Update ${solutionType} solution type calculator layouts to Show Margin only`, () => {
            cy.visit('/solution-types');
            cy.get('#datatable-search').type(type);
            cy.get('#datatable-btn-0-edit').click();

            cy.get('#2-2').click();

            cy.selectOption('#solution_type-calculator-layout', 'Show Margin Only');

            cy.get('#btn-save-solution-type').click();

            cy.visit('/');
            cy.get('#datatable-search').type(type);
            cy.get('#datatable-btn-0-show').click();

            cy.get('.commission-calculator')
                .eq(0)
                .within(() => {
                    cy.get('#commission_text').should('not.exist');
                    cy.get('#margin_text').should('exist');
                    cy.get('#input-commission_value-order').should('not.exist');
                    cy.get('#input-margin-order').should('exist');
                });
        });

        it(`Update solution types calculator layouts to Show Commission only`, () => {
            cy.visit('/solution-types');
            cy.get('#datatable-search').type(type);
            cy.get('#datatable-btn-0-edit').click();

            cy.get('#2-2').click();

            cy.selectOption('#solution_type-calculator-layout', 'Show Commission only');

            cy.get('#btn-save-solution-type').click();

            cy.visit('/');

            cy.get('#datatable-search').type(type);
            cy.get('#datatable-btn-0-show').click();

            cy.get('.commission-calculator')
                .eq(0)
                .within(() => {
                    cy.get('#commission_text').should('exist');
                    cy.get('#margin_text').should('not.exist');
                    cy.get('#input-commission_value-order').should('exist');
                    cy.get('#input-margin-order').should('not.exist');
                });
        });

        it(`Update solution types calculator layouts to Default value (Show Margin and Commission)`, () => {
            cy.visit('/solution-types');
            cy.get('#datatable-search').type(type);
            cy.get('#datatable-btn-0-edit').click();

            cy.get('#2-2').click();

            cy.selectOption('#solution_type-calculator-layout', 'Show Margin and Commission');

            cy.get('#btn-save-solution-type').click();

            cy.visit('/');
            cy.get('#datatable-search').type(type);
            cy.get('#datatable-btn-0-show').click();

            cy.get('.commission-calculator')
                .eq(0)
                .within(() => {
                    cy.get('#commission_text').should('exist');
                    cy.get('#margin_text').should('exist');
                    cy.get('#input-commission_value-order').should('exist');
                    cy.get('#input-margin-order').should('exist');
                });
        });
    });
});
