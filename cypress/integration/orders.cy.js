describe("orders", () => {

    beforeEach(() => {
        cy.login("companyadmin@example.com", "werwerwer")
    })

    // it("Dashboard doesn't contain orders", () => {
    //     cy.visit("/")
    //     cy.contains("No data available in table")
    // })

    it("Dashboard contains orders", () => {
        cy.visit("/")
        cy.contains("No data available in table")
        cy.get("#create-tab").click()
    })
});