const crypto = require("crypto");

const user = {
    name: 'Order Manager',
    email: 'orders@example.com',
    password: 'werwerwer'
}

describe("Create Order Manager User.cy", () => {
    it("Creates Order Manager User", () => {
        cy.login("super@example.com", "werwerwer")
        cy.visit("/users")
        cy.get("#link-to-create-user").click()

        cy.get("#user-name").type(user.name)
        cy.get("#user-email").type(user.email)
        cy.get("#user-password").type(user.password)
        cy.get("#user-role").select("Order Manager")
        cy.get("#btn-save-user").click()
        cy.get("#treetable-search").type(user.name)
        cy.get("#users-table").contains(user.name)
    })

    it("Check if user was created", () => {
        cy.login("super@example.com", "werwerwer")
        cy.visit("/users")
        cy.get("#treetable-search").type(user.name)
        cy.get("#users-table").contains(user.name)
    })
});

