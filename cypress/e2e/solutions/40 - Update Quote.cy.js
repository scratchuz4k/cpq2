describe('40 - Update Quote.cy', () => {
    beforeEach(() => {
        cy.login("companyadmin@example.com", "werwerwer")
    })

    it("Check Quote Update Buttons", () => {
        cy.visit("/")
        cy.get("#datatable-btn-0-crete-order").should('not.exist');
        cy.get("#datatable-btn-0-edit").should('not.exist');
        cy.get("#datatable-btn-0-delete").should('not.exist');
    })
})
