package edu.tamucc.hri.griidc.pubs;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Collections;
import java.util.Iterator;
import java.util.Set;
import java.util.TreeSet;

import edu.tamucc.hri.griidc.rdbms.RdbmsConnection;
import edu.tamucc.hri.griidc.rdbms.RdbmsConstants;
import edu.tamucc.hri.griidc.rdbms.RdbmsUtils;

public class PublicationCache {
	
	private static String PubNumberColName = RdbmsConstants.GriidcPublication_Number_ColName;
	private static String PubAuthorsColName = RdbmsConstants.GriidcPublication_Authors_ColName;
	private static String PubTitleColName = RdbmsConstants.GriidcPublication_Title_ColName;
	private static String PubJournalNameColName = RdbmsConstants.GriidcPublication_JournalName_ColName;
	private static String PubYearColName = RdbmsConstants.GriidcPublication_Year_ColName;
	private static String PubAbstractColName = RdbmsConstants.GriidcPublication_Abstract_ColName;
	private static String PubDoiColName = RdbmsConstants.GriidcPublication_DOI_ColName;
	private static RdbmsConnection griidcDbConn = null;

	private Set<Publication> pubs = null;

	public PublicationCache() {
		// TODO Auto-generated constructor stub
	}

	public Publication findPublicationSerialNumber(int serialNumber) {
		if(this.pubs == null) {
		   this.cachePublications();
		}
		Iterator<Publication> it = this.pubs.iterator();
		while(it.hasNext()) {
			Publication p = it.next();
			if(p.getSerialNumber() == serialNumber) return p;
		}
		return null;
	}
	
	public Publication findPublicationDoi(String doi) {
		if(this.pubs == null) {
		   this.cachePublications();
		}
		Iterator<Publication> it = this.pubs.iterator();
		while(it.hasNext()) {
			Publication p = it.next();
			if(p.getDoi().equals(doi)) return p;
		}
		return null;
	}
	/**
	 * return a cached Set of Publications
	 * 
	 * @return
	 */
	public Set<Publication> cachePublications() {
		this.pubs = Collections.synchronizedSortedSet(new TreeSet<Publication>());

		try {
			RdbmsConnection dbCon = RdbmsUtils.getGriidcDbConnectionInstance();
			String query = "SELECT * FROM "
					+ RdbmsConnection
							.wrapInDoubleQuotes(RdbmsConstants.GriidcPublicationTableName);
			ResultSet rs = dbCon.executeQueryResultSet(query);
			while (rs.next()) {
				Publication pub = new Publication();
				pub.setSerialNumber(rs.getInt(PubNumberColName));
				pub.setAuthor(rs.getString(PubAuthorsColName));
				pub.setTitle(rs.getString(PubTitleColName));
				pub.setPublisher(rs.getString(PubJournalNameColName));
				pub.setPublicationYear(rs.getInt(PubYearColName));
				pub.setAbstract(rs.getString(PubAbstractColName));
				pub.setDoi(rs.getString(PubDoiColName));
				pubs.add(pub);
			}
		} catch (SQLException e) {
			String msg = "PublicationCache.getPubNumbers()";
			System.err.println(msg + " - " + e.getMessage());
			System.exit(-1);
		}
		return pubs;
	}
	
	public Publication[] toArray() {
		Publication[] pa = new Publication[this.cachePublications().size()];
		pa = this.pubs.toArray(pa);
		return pa;
	}
	public static void main(String[] args) {
		PublicationCache pc = new PublicationCache();
		 Publication[] pa = pc.toArray();
		 for(Publication pub : pa) {
			 System.out.println(pub.toShortString());
		 }
	}
}
