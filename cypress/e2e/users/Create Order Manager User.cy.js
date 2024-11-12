const crypto = require("crypto");
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


describe("Create Order Manager User.cy", () => {
    it("Creates Order Manager User", () => {
        cy.login("companyadmin@example.com", "werwerwer")
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
        cy.login("companyadmin@example.com", "werwerwer")
        cy.visit("/users")
        cy.get("#treetable-search").type(user.name)
        cy.get("#users-table").contains(user.name)
    })
});

