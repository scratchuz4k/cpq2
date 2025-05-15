const { getCurrentDate, generateHash, slugify } = require("../../support/utils.js");
const temp = getCurrentDate()
const user = { name: slugify(temp), email: slugify(temp) + "@email.com", password: generateHash(temp), role: 'Support Agent' }

const temp1 = getCurrentDate();
const token_name = generateHash(temp1);



describe('', () => {
    beforeEach(() => {
        cy.login('super@example.com', 'werwerwer')
    })

    it('Create Support user ', () => {
        cy.visit("/users")
        cy.get("#link-to-create-user").click()

        cy.get("#user-name").type(user.name)
        cy.get("#user-email").type(user.email)
        cy.get("#user-password").type(user.password)
        cy.get("#user-role").select(user.role)
        cy.get("#btn-save-user").click()
        cy.get("#treetable-search").type(user.name)
        cy.get("#users-table").contains(user.name)
    });

    it('Create a token', () => {
        cy.login(user.email, user.password)
        cy.visit('/')
        cy.get('#my-account').click()
        cy.get('#link-tokens').click()
        cy.get('#token-create-btn').click()

        cy.get("#token-name").type(token_name)
        cy.get("#token-user")
            .find('option')
            .contains(user.name)
            .then($op => {
                cy.get('#token-user').select($op.text())
            })
    })
})
