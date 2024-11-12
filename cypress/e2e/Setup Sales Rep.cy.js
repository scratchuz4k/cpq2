const crypto = require("crypto");
const rand = (notAllowedNumber, max = 101) => {
    let randomNumber = Math.floor(Math.random() * max); // Generate a random number

    // Keep generating until it"s not equal to the not allowed number
    while (randomNumber === notAllowedNumber) {
        randomNumber = Math.floor(Math.random() * max);
    }
    return randomNumber
}
// Function to generate hash
const generateHash = (input) => {
    return crypto.createHash("md5").update(input).digest("hex"); // SHA-256 hash
}
const getCurrentDate = () => {
    const currentDate = new Date();
    const year = currentDate.getFullYear();
    const month = String(currentDate.getMonth() + 1).padStart(2, "0");
    const day = String(currentDate.getDate()).padStart(2, "0");
    const hour = String(currentDate.getHours()).padStart(2, "0");
    const minute = String(currentDate.getMinutes()).padStart(2, "0");
    const millisecond = String(currentDate.getMilliseconds()).padStart(3, "0");

    // Create a readable date string
    const readableDate = `${year}-${month}-${day} ${hour}:${minute}:${millisecond}`;

    return readableDate;
}

const slugify = (string) => {
    return string
        .toLowerCase() // Converte para letras minúsculas
        .trim() // Remove espaços em branco no início e no final
        .replace(/[^\w\s-]/g, '') // Remove caracteres especiais (exceto espaços e hífens)
        .replace(/\s+/g, '-') // Substitui espaços por hífens
        .replace(/-+/g, '-'); // Remove múltiplos hífens consecutivos
}
const temp = getCurrentDate()
const user = { name: slugify(temp), email: slugify(temp) + "@email.com", password: generateHash(temp) }
const account = { ref: generateHash(temp), name: temp }


describe("Setup Sales Rep.cy", () => {
    it("Creates an User", () => {
        cy.login("super@example.com", "werwerwer")
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-users").click()
        cy.get("#link-to-create-user").click()

        cy.get("#user-name").type(user.name)
        cy.get("#user-email").type(user.email)
        cy.get("#user-password").type(user.password)
        cy.get("#user-role").select("Sales Rep")
        cy.get("#user-commission-rate").type(5)
        cy.get("#btn-save-user").click()
        cy.get("#treetable-search").type(user.name)
        cy.get("#users-table").contains(user.name)
    })
    it("Creates an Order and checks comission", () => {
        cy.login(user.email, user.password)
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#link-to-create-account").click()
        cy.get("#account-name").type(account.name)
        cy.get("#account-reference").type(account.ref)
        cy.get("#save-account-button").click()
        cy.get(".notyf").contains("Created with Success")
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#account_id").select(account.name)
        cy.get("#solution_id").select("Mobile")
        cy.get("#btn-create-quote").click()
        cy.visit("/")
        cy.get("#datatable-btn-0-show").click()
        cy.get("#order-tax-comission").invoke("text")
            .then((text) => {
                expect(text).to.not.equal(5);
            });
    })
});

