package dao;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class AppointmentDAO {

    public void createAppointment(int petId, String date, String description) throws SQLException {
        String sql = "INSERT INTO appointments (pet_id, date, description) VALUES (?, ?, ?)";
        try (Connection conn = DBConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, petId);
            stmt.setString(2, date);
            stmt.setString(3, description);
            stmt.executeUpdate();
        }
    }

    public List<String> getAppointments() throws SQLException {
        List<String> appointments = new ArrayList<>();
        String sql = "SELECT * FROM appointments";
        try (Connection conn = DBConnection.getConnection();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {
            while (rs.next()) {
                appointments.add("ID: " + rs.getInt("id") +
                                 ", Pet ID: " + rs.getInt("pet_id") +
                                 ", Date: " + rs.getString("date") +
                                 ", Description: " + rs.getString("description"));
            }
        }
        return appointments;
    }
}
