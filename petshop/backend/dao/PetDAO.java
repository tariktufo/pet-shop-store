package dao;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class PetDAO {

    public void createPet(String name, String type, int age, int ownerId) throws SQLException {
        String sql = "INSERT INTO pets (name, type, age, owner_id) VALUES (?, ?, ?, ?)";
        try (Connection conn = DBConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, name);
            stmt.setString(2, type);
            stmt.setInt(3, age);
            stmt.setInt(4, ownerId);
            stmt.executeUpdate();
        }
    }

    public List<String> getPets() throws SQLException {
        List<String> pets = new ArrayList<>();
        String sql = "SELECT * FROM pets";
        try (Connection conn = DBConnection.getConnection();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {
            while (rs.next()) {
                pets.add("ID: " + rs.getInt("id") +
                         ", Name: " + rs.getString("name") +
                         ", Type: " + rs.getString("type") +
                         ", Age: " + rs.getInt("age") +
                         ", Owner ID: " + rs.getInt("owner_id"));
            }
        }
        return pets;
    }
}
